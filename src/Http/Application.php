<?php

declare(strict_types=1);

namespace App\Http;

use App\Config\Env;
use App\Database\Connection;
use App\Repository\ProductRepository;
use App\Service\AuthService;
use App\Service\PurchaseService;
use App\Support\Csrf;
use App\Support\Flash;
use App\Support\View;
use App\Validation\Ean13Validator;
use App\Validation\ProductValidator;
use PDOException;
use Throwable;

final class Application
{
    private ProductRepository $products;
    private AuthService $auth;

    public function __construct()
    {
        $db = Connection::get();
        $this->products = new ProductRepository($db);
        $this->auth = new AuthService($db);
    }

    public function run(): void
    {
        $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $base = trim((string) parse_url(Env::get('APP_URL', ''), PHP_URL_PATH), '/');
        if ($base !== '' && str_starts_with($path, $base)) $path = trim(substr($path, strlen($base)), '/');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        try {
            match ([$method, $path]) {
                ['GET', ''], ['GET', 'catalogo'] => $this->catalogue(),
                ['GET', 'producto'] => $this->product(),
                ['POST', 'comprar'] => $this->purchase(),
                ['GET', 'admin/login'] => View::render('admin/login', ['title' => 'Acceso de administración']),
                ['POST', 'admin/login'] => $this->login(),
                ['POST', 'admin/logout'] => $this->logout(),
                ['GET', 'admin'] => $this->admin(),
                ['GET', 'admin/productos/nuevo'] => $this->productForm(),
                ['POST', 'admin/productos/nuevo'] => $this->saveProduct(),
                ['GET', 'admin/productos/editar'] => $this->productForm((int) ($_GET['id'] ?? 0)),
                ['POST', 'admin/productos/editar'] => $this->saveProduct((int) ($_GET['id'] ?? 0)),
                ['POST', 'admin/productos/eliminar'] => $this->deleteProduct(),
                ['GET', 'api/productos'] => $this->apiProducts(),
                ['GET', 'api/productos/ean'] => $this->apiEan(),
                default => $this->notFound(),
            };
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->serverError('No ha sido posible conectar con la tienda. Inténtalo de nuevo más tarde.');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->serverError(Env::bool('APP_DEBUG') ? $exception->getMessage() : 'Ha ocurrido un error inesperado.');
        }
    }

    private function catalogue(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        View::render('catalogue', ['title' => 'Catálogo', 'products' => $this->products->search($query), 'query' => $query]);
    }

    private function product(): void
    {
        $product = $this->products->find((int) ($_GET['id'] ?? 0));
        if (!$product) { $this->notFound(); return; }
        View::render('product', ['title' => $product['nombre'], 'product' => $product]);
    }

    private function purchase(): void
    {
        $this->requireCsrf();
        $id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
        if (!$id || !$quantity) {
            Flash::set('error', 'Selecciona un producto y una cantidad válida.');
            redirect('catalogo');
        }
        try {
            $this->enforcePurchaseLimit();
            $saleId = (new PurchaseService(Connection::get(), $this->products))->purchase($id, $quantity);
            $_SESSION['purchases'][] = time();
            Flash::set('success', "Compra de demostración #{$saleId} realizada correctamente.");
        } catch (\DomainException $exception) {
            Flash::set('error', $exception->getMessage());
        }
        redirect('producto?id=' . $id);
    }

    private function login(): void
    {
        $this->requireCsrf();
        if ($this->auth->attempt((string) ($_POST['usuario'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            Flash::set('success', 'Sesión iniciada.');
            redirect('admin');
        }
        Flash::set('error', 'Usuario o contraseña incorrectos.');
        redirect('admin/login');
    }

    private function logout(): void
    {
        $this->requireCsrf();
        AuthService::logout();
        Flash::set('success', 'Sesión cerrada.');
        redirect('catalogo');
    }

    private function admin(): void
    {
        $this->requireAdmin();
        View::render('admin/index', ['title' => 'Administración', 'products' => $this->products->search('', true)]);
    }

    private function productForm(int $id = 0): void
    {
        $this->requireAdmin();
        $product = $id ? $this->products->find($id) : null;
        if ($id && !$product) { $this->notFound(); return; }
        View::render('admin/product-form', ['title' => $id ? 'Editar producto' : 'Nuevo producto', 'product' => $product, 'errors' => [], 'old' => $product ?? []]);
    }

    private function saveProduct(int $id = 0): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $errors = ProductValidator::validate($_POST);
        $existing = $this->products->findByEan(trim((string) ($_POST['ean13'] ?? '')));
        if ($existing && (int) $existing['id'] !== $id) $errors['ean13'] = 'Ese EAN-13 ya pertenece a otro producto.';
        if ($errors) {
            http_response_code(422);
            View::render('admin/product-form', ['title' => $id ? 'Editar producto' : 'Nuevo producto', 'product' => $id ? $this->products->find($id) : null, 'errors' => $errors, 'old' => $_POST]);
            return;
        }
        $id ? $this->products->update($id, $_POST) : $this->products->create($_POST);
        Flash::set('success', $id ? 'Producto actualizado.' : 'Producto creado.');
        redirect('admin');
    }

    private function deleteProduct(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        try {
            $deleted = $this->products->delete((int) ($_POST['id'] ?? 0));
            Flash::set($deleted ? 'success' : 'error', $deleted ? 'Producto eliminado.' : 'El producto no existe.');
        } catch (PDOException) {
            Flash::set('error', 'No se puede eliminar un producto que ya tiene ventas. Déjalo sin stock.');
        }
        redirect('admin');
    }

    private function apiProducts(): void
    {
        $this->json($this->products->search(trim((string) ($_GET['q'] ?? ''))));
    }

    private function apiEan(): void
    {
        $ean = trim((string) ($_GET['ean13'] ?? ''));
        if (!Ean13Validator::isValid($ean)) { $this->json(['error' => 'EAN-13 no válido.'], 422); return; }
        $product = $this->products->findByEan($ean);
        $this->json($product ?: ['error' => 'Producto no encontrado.'], $product ? 200 : 404);
    }

    private function requireAdmin(): void
    {
        if (!AuthService::check()) { Flash::set('error', 'Inicia sesión para continuar.'); redirect('admin/login'); }
    }

    private function requireCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) { http_response_code(419); View::render('error', ['title' => 'Sesión caducada', 'message' => 'Actualiza la página e inténtalo de nuevo.']); exit; }
    }

    private function enforcePurchaseLimit(): void
    {
        if (!Env::bool('DEMO_MODE', true)) return;
        $window = time() - 3600;
        $_SESSION['purchases'] = array_values(array_filter($_SESSION['purchases'] ?? [], fn ($time) => $time >= $window));
        if (count($_SESSION['purchases']) >= Env::int('DEMO_PURCHASE_LIMIT', 5)) throw new \DomainException('Has alcanzado el límite de compras de demostración por hora.');
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function notFound(): void
    {
        http_response_code(404);
        View::render('error', ['title' => 'Página no encontrada', 'message' => 'La página que buscas no existe.']);
    }

    private function serverError(string $message): void
    {
        http_response_code(500);
        View::render('error', ['title' => 'Algo ha fallado', 'message' => $message]);
    }
}
