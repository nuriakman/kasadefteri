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

// Middleware pipeline'ı oluştur
$pipeline = [
    new SecurityHeadersMiddleware(),
    new RateLimitMiddleware(60, 60), // Dakikada 60 istek
    new AuthMiddleware(),
    new CsrfMiddleware()
];

// İstek yoluna göre ek middleware'leri ekle
$path = $_SERVER['REQUEST_URI'];

// Admin rotaları için rol kontrolü
if (strpos($path, '/admin') === 0) {
    $pipeline[] = new RoleMiddleware(['admin', 'superadmin']);
}

// SuperAdmin rotaları için rol kontrolü
if (strpos($path, '/superadmin') === 0) {
    $pipeline[] = new RoleMiddleware(['superadmin']);
}

// Validasyon kuralları
$validationRules = [
    '/auth/login' => [
        'email' => 'required|email',
        'password' => 'required|min:6'
    ],
    '/transactions' => [
        'amount' => 'required|numeric',
        'type' => 'required|in:income,expense',
        'description' => 'required|max:255|xss',
        'category_id' => 'required|numeric',
        'register_id' => 'required|numeric'
    ],
    '/categories' => [
        'name' => 'required|max:50|xss',
        'type' => 'required|in:income,expense'
    ]
];

// İstek yoluna göre validasyon middleware'i ekle
if (isset($validationRules[$path])) {
    $pipeline[] = new ValidationMiddleware($validationRules[$path]);
}

// Middleware pipeline'ı çalıştır
$request = [];
foreach ($pipeline as $middleware) {
    $request = $middleware->handle($request, function($req) {
        return $req;
    });
}

// Veritabanı bağlantısı
$database = new Database();
$db = $database->connect();

// URL'yi parçala
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Endpoint'leri yönlendir
try {
    switch ($uri[2]) {
        case 'auth':
            $authController = new AuthController($db);
            
            switch ($uri[3] ?? '') {
                case 'login':
                    $authController->login();
                    break;
                case 'google':
                    $authController->googleLogin();
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['message' => 'Endpoint bulunamadı']);
                    break;
            }
            break;

        case 'transactions':
            $transactionController = new TransactionController($db);
            
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    if (isset($uri[3]) && $uri[3] === 'day-end') {
                        $transactionController->markDayEnd();
                    } else {
                        $transactionController->create();
                    }
                    break;
                case 'GET':
                    $transactionController->getDailyTransactions();
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['message' => 'Geçersiz metod']);
                    break;
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['message' => 'Endpoint bulunamadı']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
