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
                ['GET', 'admin/login'] => View::render('admin/login', ['title' => 'Admin access']),
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
            $this->serverError('The shop could not connect to the database. Please try again later.');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->serverError(Env::bool('APP_DEBUG') ? $exception->getMessage() : 'An unexpected error occurred.');
        }
    }

    private function catalogue(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        View::render('catalogue', ['title' => 'Catalogue', 'products' => $this->products->search($query), 'query' => $query]);
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
            Flash::set('error', 'Select a product and a valid quantity.');
            redirect('catalogo');
        }
        try {
            $this->enforcePurchaseLimit();
            $saleId = (new PurchaseService(Connection::get(), $this->products))->purchase($id, $quantity);
            $_SESSION['purchases'][] = time();
            Flash::set('success', "Demo purchase #{$saleId} completed successfully.");
        } catch (\DomainException $exception) {
            Flash::set('error', $exception->getMessage());
        }
        redirect('producto?id=' . $id);
    }

    private function login(): void
    {
        $this->requireCsrf();
        if ($this->auth->attempt((string) ($_POST['usuario'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            Flash::set('success', 'Session started.');
            redirect('admin');
        }
        Flash::set('error', 'Invalid username or password.');
        redirect('admin/login');
    }

    private function logout(): void
    {
        $this->requireCsrf();
        AuthService::logout();
        Flash::set('success', 'Session closed.');
        redirect('catalogo');
    }

    private function admin(): void
    {
        $this->requireAdmin();
        View::render('admin/index', ['title' => 'Admin', 'products' => $this->products->search('', true)]);
    }

    private function productForm(int $id = 0): void
    {
        $this->requireAdmin();
        $product = $id ? $this->products->find($id) : null;
        if ($id && !$product) { $this->notFound(); return; }
        View::render('admin/product-form', ['title' => $id ? 'Edit product' : 'New product', 'product' => $product, 'errors' => [], 'old' => $product ?? []]);
    }

    private function saveProduct(int $id = 0): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        $errors = ProductValidator::validate($_POST);
        $existing = $this->products->findByEan(trim((string) ($_POST['ean13'] ?? '')));
        if ($existing && (int) $existing['id'] !== $id) $errors['ean13'] = 'That EAN-13 already belongs to another product.';
        if ($errors) {
            http_response_code(422);
            View::render('admin/product-form', ['title' => $id ? 'Edit product' : 'New product', 'product' => $id ? $this->products->find($id) : null, 'errors' => $errors, 'old' => $_POST]);
            return;
        }
        $id ? $this->products->update($id, $_POST) : $this->products->create($_POST);
        Flash::set('success', $id ? 'Product updated.' : 'Product created.');
        redirect('admin');
    }

    private function deleteProduct(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();
        try {
            $deleted = $this->products->delete((int) ($_POST['id'] ?? 0));
            Flash::set($deleted ? 'success' : 'error', $deleted ? 'Product deleted.' : 'Product does not exist.');
        } catch (PDOException) {
            Flash::set('error', 'Products with existing sales cannot be deleted. Set stock to zero instead.');
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
        if (!Ean13Validator::isValid($ean)) { $this->json(['error' => 'Invalid EAN-13.'], 422); return; }
        $product = $this->products->findByEan($ean);
        $this->json($product ?: ['error' => 'Product not found.'], $product ? 200 : 404);
    }

    private function requireAdmin(): void
    {
        if (!AuthService::check()) { Flash::set('error', 'Log in to continue.'); redirect('admin/login'); }
    }

    private function requireCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) { http_response_code(419); View::render('error', ['title' => 'Session expired', 'message' => 'Refresh the page and try again.']); exit; }
    }

    private function enforcePurchaseLimit(): void
    {
        if (!Env::bool('DEMO_MODE', true)) return;
        $window = time() - 3600;
        $_SESSION['purchases'] = array_values(array_filter($_SESSION['purchases'] ?? [], fn ($time) => $time >= $window));
        if (count($_SESSION['purchases']) >= Env::int('DEMO_PURCHASE_LIMIT', 5)) throw new \DomainException('You have reached the hourly demo purchase limit.');
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
        View::render('error', ['title' => 'Page not found', 'message' => 'The page you are looking for does not exist.']);
    }

    private function serverError(string $message): void
    {
        http_response_code(500);
        View::render('error', ['title' => 'Something went wrong', 'message' => $message]);
    }
}
