<?php

error_reporting(E_ALL & ~E_DEPRECATED);

/**
 * POS System V2 - Central Route Handler
 * Uses FastRoute to map URLs to controller methods.
 */

// CORS headers for localhost requests (Vue frontend on different port)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1|::1)(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

/**
 * Define all routes
 */
$dispatcher = simpleDispatcher(function (RouteCollector $r) {

    // ==================== AUTH ====================
    $r->addRoute('POST', '/auth/login', ['App\Controllers\AuthController', 'login']);
    $r->addRoute('POST', '/auth/register', ['App\Controllers\AuthController', 'register']);
    $r->addRoute('GET', '/auth/me', ['App\Controllers\AuthController', 'me']);

    // ==================== USERS ====================
    $r->addRoute('GET', '/users', ['App\Controllers\UserController', 'getAll']);
    $r->addRoute('GET', '/users/drivers', ['App\Controllers\UserController', 'getDrivers']);
    $r->addRoute('GET', '/users/{id}', ['App\Controllers\UserController', 'getById']);
    $r->addRoute('POST', '/users', ['App\Controllers\UserController', 'create']);
    $r->addRoute('PUT', '/users/{id}/status', ['App\Controllers\UserController', 'updateStatus']);
    $r->addRoute('PUT', '/users/{id}/profile', ['App\Controllers\UserController', 'updateProfile']);
    $r->addRoute('POST', '/users/reset-password', ['App\Controllers\UserController', 'resetPassword']);

    // ==================== PRODUCTS ====================
    $r->addRoute('GET', '/products', ['App\Controllers\ProductController', 'getAll']);
    $r->addRoute('GET', '/products/categories', ['App\Controllers\ProductController', 'getCategories']);
    $r->addRoute('GET', '/products/sales/top', ['App\Controllers\ProductController', 'getTopFive']);
    $r->addRoute('GET', '/products/sales/today', ['App\Controllers\ProductController', 'getTodaysSales']);
    $r->addRoute('GET', '/products/code/{code}', ['App\Controllers\ProductController', 'getByCode']);
    $r->addRoute('GET', '/products/{id}', ['App\Controllers\ProductController', 'getById']);
    $r->addRoute('POST', '/products', ['App\Controllers\ProductController', 'create']);
    $r->addRoute('PUT', '/products/{id}', ['App\Controllers\ProductController', 'update']);
    $r->addRoute('DELETE', '/products/{id}', ['App\Controllers\ProductController', 'delete']);
    $r->addRoute('POST', '/products/sales', ['App\Controllers\ProductController', 'completeSale']);

    // ==================== DASHBOARD ====================
    $r->addRoute('GET', '/dashboard/summary', ['App\Controllers\ProductController', 'getDashboardSummary']);

    // ==================== ORDERS ====================
    $r->addRoute('GET', '/orders', ['App\Controllers\OrderController', 'getAll']);
    $r->addRoute('GET', '/orders/my', ['App\Controllers\OrderController', 'getMyOrders']);
    $r->addRoute('GET', '/orders/{id}', ['App\Controllers\OrderController', 'getById']);
    $r->addRoute('POST', '/orders', ['App\Controllers\OrderController', 'create']);
    $r->addRoute('PUT', '/orders/{id}/status', ['App\Controllers\OrderController', 'updateStatus']);
    $r->addRoute('PUT', '/orders/{id}/driver', ['App\Controllers\OrderController', 'assignDriver']);

    // ==================== PAYMENTS ====================
    $r->addRoute('POST', '/payments/create-intent', ['App\Controllers\PaymentController', 'createPaymentIntent']);
});

/**
 * Dispatch the request
 */
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
    case FastRoute\Dispatcher::FOUND:
        $class = $routeInfo[1][0];
        $method = $routeInfo[1][1];
        $controller = new $class();
        $vars = $routeInfo[2];
        $controller->$method($vars);
        break;
}
