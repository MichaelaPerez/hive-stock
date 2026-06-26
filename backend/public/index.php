<?php

use HiveStock\Config\Database;
use HiveStock\Config\Env;
use HiveStock\Controllers\ItemController;
use HiveStock\Controllers\LocationController;
use HiveStock\Http\Request;
use HiveStock\Http\Response;
use HiveStock\Http\Router;

require_once __DIR__ . '/../bootstrap.php';

Env::load(__DIR__ . '/../.env');

$origin = Env::get('CORS_ORIGIN', 'http://localhost:5173');
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$request = Request::capture();
$router = new Router();

$items = new ItemController();
$locations = new LocationController();

$router->get('/api/v1/health', function (Request $request): Response {
    Database::connection()->query('SELECT 1');

    return Response::json([
        'data' => [
            'status' => 'ok',
            'database' => 'connected',
        ],
    ]);
});

$router->get('/api/v1/items', [$items, 'index']);
$router->get('/api/v1/items/barcode/{barcode}', [$items, 'getByBarcode']);
$router->get('/api/v1/items/name/{name}', [$items, 'getByName']);
$router->post('/api/v1/items', [$items, 'create']);
$router->patch('/api/v1/items/{barcode}', [$items, 'updateLocation']);

$router->get('/api/v1/locations', [$locations, 'index']);
$router->get('/api/v1/locations/barcode/{barcode}', [$locations, 'getByBarcode']);
$router->get('/api/v1/locations/name/{name}', [$locations, 'getByName']);
$router->get('/api/v1/locations/{barcode}/items', [$locations, 'items']);
$router->post('/api/v1/locations', [$locations, 'create']);
$router->patch('/api/v1/locations/{barcode}', [$locations, 'updateParent']);

try {
    $router->dispatch($request)->send();
} catch (Throwable $exception) {
    Response::jsonError('Internal server error', 500, 'internal_server_error')->send();
}
?>