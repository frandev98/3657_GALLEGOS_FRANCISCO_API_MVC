<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\CorsMiddleware;
use Config\Env;
use Config\Database;
use Core\Router;
use Core\Response;

// Cargar variables de entorno
Env::load(__DIR__ . '/../.env');

// Configurar cabeceras CORS
CorsMiddleware::handle();

try {
    $database = new Database();
    $db = $database->connect();
} catch (Throwable $e) {
    Response::error($e->getMessage(), 500);
}

$router = new Router();

require_once __DIR__ . '/../routes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Detección dinámica del directorio base (funciona con cualquier nombre de carpeta)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

if ($uri === '') {
    $uri = '/';
}

$router->dispatch($method, $uri);