<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Middleware\AuthMiddleware;
use Middleware\RoleMiddleware;
use Middleware\RateLimitMiddleware;
use Middleware\ValidationMiddleware;
use Middleware\CsrfMiddleware;
use Middleware\SecurityHeadersMiddleware;
use App\Config\Database;
use App\Controllers\AuthController;
use App\Controllers\TransactionController;
use App\Core\Router;

// CORS ayarları
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// OPTIONS isteklerini yanıtla
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// .env dosyasını yükle
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Session başlat
session_start();

// Veritabanı bağlantısı
$database = new Database();
$db = $database->connect();

// Router'ı başlat
$router = new Router();

// Global middleware'leri ekle
$router->middleware([
    new SecurityHeadersMiddleware(),
    new RateLimitMiddleware(60, 60),
    new AuthMiddleware(),
    new CsrfMiddleware()
]);

// Auth rotaları
$router->group('/auth', function($router) {
    $router->post('/login', [AuthController::class, 'login']);
    $router->post('/google', [AuthController::class, 'googleLogin']);
});

// İşlem rotaları
$router->group('/transactions', function($router) {
    $router->middleware([new ValidationMiddleware([
        'amount' => 'required|numeric',
        'type' => 'required|in:income,expense',
        'description' => 'required|max:255|xss',
        'category_id' => 'required|numeric',
        'register_id' => 'required|numeric'
    ])]);

    $router->post('/', [TransactionController::class, 'create']);
    $router->post('/day-end', [TransactionController::class, 'markDayEnd']);
    $router->get('/', [TransactionController::class, 'getDailyTransactions']);
});

// Admin rotaları
$router->group('/admin', function($router) {
    $router->middleware([new RoleMiddleware(['admin', 'superadmin'])]);
    // Admin rotalarını buraya ekle
});

// SuperAdmin rotaları
$router->group('/superadmin', function($router) {
    $router->middleware([new RoleMiddleware(['superadmin'])]);
    // SuperAdmin rotalarını buraya ekle
});

try {
    // İsteği işle
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $response = $router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
    
    // JSON yanıtı döndür
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (\Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
