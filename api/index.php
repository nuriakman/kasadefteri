<?php
// CORS ayarları
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// .env dosyasını yükle
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Gelen isteğin yolunu al
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// API rotaları
if (strpos($path, '/docs/') === 0) {
  // Docs klasöründeki istekleri işle
  $docs_path = __DIR__ . '/docs' . str_replace('/docs', '', $path);
  if (is_file($docs_path)) {
    // Dosya türüne göre Content-Type ayarla
    $extension = pathinfo($docs_path, PATHINFO_EXTENSION);
    switch ($extension) {
      case 'css':
        header('Content-Type: text/css');
        break;
      case 'js':
        header('Content-Type: application/javascript');
        break;
      case 'yaml':
        header('Content-Type: application/x-yaml');
        break;
      case 'html':
      case 'php':
        header('Content-Type: text/html');
        break;
    }
    include $docs_path;
    exit;
  }
}

// Diğer API endpoint'leri buraya eklenecek
// Örnek: /auth/login, /auth/google, /transactions vb.

// 404 hatası
header('HTTP/1.1 404 Not Found');
echo json_encode(['error' => 'Endpoint bulunamadı']);
