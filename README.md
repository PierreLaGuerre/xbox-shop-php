# Xbox Shop · PHP + MariaDB

Tienda de videojuegos ficticia creada como proyecto de portfolio tras finalizar DAW. El objetivo no es simular un e-commerce real, sino enseñar de forma clara fundamentos de **PHP 8**, **PDO**, **MariaDB**, seguridad web y frontend responsive sin frameworks.

> **Fan project educativo no oficial.** No está afiliado, respaldado ni patrocinado por Microsoft o Xbox. Los productos y nombres del catálogo son ficticios.

![Catálogo de Xbox Shop](docs/screenshots/catalogue.png)

## Qué demuestra

- Catálogo responsive con búsqueda por nombre o EAN-13.
- Compra de demostración con bloqueo de fila, actualización de stock y registro de venta en una transacción.
- CRUD de productos protegido por autenticación con sesiones.
- Consultas preparadas, validación servidor, escape HTML y tokens CSRF.
- API JSON de catálogo y consulta por EAN-13.
- Restauración programable de los datos de demostración.
- Pruebas unitarias e integración contra MariaDB mediante GitHub Actions.

## Arquitectura

```text
public/       Front controller y recursos web
src/          Configuración, HTTP, repositorios, servicios y validadores
templates/    Vistas PHP y layout común
database/     Esquema y datos ficticios reproducibles
bin/          Creación de administrador, lint y restauración de demo
tests/        Pruebas unitarias y de integración
```

El flujo principal es `Application → Service → Repository → PDO`. Se ha mantenido intencionadamente pequeño para que cada decisión resulte fácil de explicar y probar.

## Instalación local con XAMPP/phpMyAdmin

Requisitos: PHP 8.2 o posterior, Composer, MariaDB/MySQL y las extensiones `pdo_mysql` y `mbstring`.

1. Clona el repositorio dentro de `htdocs` y entra en la carpeta.
2. Instala dependencias:

   ```bash
   composer install
   ```

3. Copia `.env.example` como `.env` y configura la URL y las credenciales locales. La contraseña de ejemplo no debe reutilizarse.
4. En phpMyAdmin crea una base `xbox_shop` con cotejamiento `utf8mb4_unicode_ci` e importa, en este orden, `database/schema.sql` y `database/seed.sql`. En XAMPP también puedes automatizar este paso con `php bin/setup-local.php`; usa `ROOT_DB_USER`/`ROOT_DB_PASS` si tu cuenta raíz no tiene la configuración predeterminada.
6. Para habilitar la administración, añade temporalmente una contraseña de 12 caracteres o más a `.env`:

   ```dotenv
   ADMIN_PASSWORD=una-clave-local-segura
   ```

   Después ejecuta `php bin/create-admin.php admin` y elimina esa línea de `.env`.
7. Abre `http://localhost/xbox-shop/public/`.

Apache debe tener `mod_rewrite` habilitado. Como alternativa de desarrollo puede usarse:

```bash
php -S 127.0.0.1:8080 -t public public/router.php
```

y establecer `APP_URL=http://127.0.0.1:8080`.

## Variables de entorno

| Variable | Uso |
|---|---|
| `APP_URL` | URL pública sin barra final |
| `APP_DEBUG` | Muestra detalles de error únicamente en local |
| `SESSION_SECURE` | Debe ser `true` bajo HTTPS |
| `DEMO_MODE` | Activa límites y permite restaurar la demo |
| `DEMO_PURCHASE_LIMIT` | Compras máximas por sesión y hora |
| `DB_*` | Conexión privada a MariaDB/MySQL |

`.env` y `vendor/` están excluidos de Git. El repositorio solo contiene `.env.example`, el esquema y datos ficticios.

## API

- `GET /api/productos`: catálogo con stock; acepta `?q=texto`.
- `GET /api/productos/ean?ean13=5901234123457`: un producto o error JSON con estado `404`/`422`.

## Calidad

```bash
composer lint
composer test
```

Las pruebas de integración se omiten localmente si no existen variables `TEST_DB_*`. En CI se crea una MariaDB desechable y se cubren compra correcta, stock insuficiente, producto inexistente y rollback.

Antes de publicar una versión se revisan también catálogo, búsqueda, compra, login/logout, CRUD, API, viewport móvil, teclado, contraste y Lighthouse.

## Despliegue gratuito en Alwaysdata

1. Crea una cuenta Free, una base MariaDB y un usuario de base de datos.
2. Clona el repositorio por SSH y ejecuta `composer install --no-dev --optimize-autoloader`.
3. Importa `database/schema.sql` y `database/seed.sql` desde el panel o la consola.
4. Crea `.env` en la raíz privada con las credenciales de Alwaysdata. Usa `SESSION_SECURE=true`, `APP_DEBUG=false` y la URL `https://usuario.alwaysdata.net`.
5. Configura el sitio como PHP y apunta su raíz a la carpeta `public/`.
6. Crea el administrador con `bin/create-admin.php` y retira `ADMIN_PASSWORD`.
7. Programa una tarea diaria `php /ruta/al/proyecto/bin/reset-demo.php`.
8. Verifica HTTPS, compra, login y API antes de enlazar la demo.

La documentación oficial de referencia está en [planes](https://www.alwaysdata.com/en/pricing/), [PHP](https://help.alwaysdata.com/en/web-hosting/languages/php/) y [MariaDB](https://help.alwaysdata.com/en/web-hosting/databases/mariadb/).

## Presentación en GitHub

Al publicar, configura la descripción como: `Tienda educativa con PHP 8, PDO, MariaDB y frontend responsive.` Añade la URL de la demo y los topics `php`, `mysql`, `pdo`, `html`, `css`, `javascript` y `portfolio`.

Incluye capturas de catálogo móvil/escritorio y del panel sin credenciales. Después selecciona el repositorio desde **Customize your pins** en el perfil de GitHub.

## Decisiones y aprendizajes

- PDO y sentencias preparadas separan los datos de las consultas.
- `SELECT … FOR UPDATE` evita que dos compras consuman simultáneamente el mismo stock.
- El patrón POST/Redirect/GET impide reenvíos accidentales al recargar.
- El EAN se trata como texto para conservar ceros iniciales.
- El administrador se crea fuera de los SQL versionados; ninguna clave llega al repositorio.
- El frontend utiliza HTML, CSS y JavaScript nativos para hacer visibles los fundamentos.

## Licencia

Código publicado bajo [MIT](LICENSE). La licencia no concede derechos sobre marcas de terceros.
