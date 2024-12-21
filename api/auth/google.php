<?php
require_once '../config/Database.php';
require_once '../models/User.php';

// CORS ayarları
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Veritabanı bağlantısı
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// POST verilerini al
$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->googleToken)) {
    http_response_code(400);
    echo json_encode(array("message" => "Google token gerekli."));
    exit();
}

try {
    // Google token'ı doğrula ve kullanıcı bilgilerini al
    $client = new Google_Client(['client_id' => getenv('GOOGLE_CLIENT_ID')]);
    $payload = $client->verifyIdToken($data->googleToken);

    if ($payload) {
        // Google kullanıcı bilgileri
        $googleData = array(
            'id' => $payload['sub'],
            'email' => $payload['email'],
            'given_name' => $payload['given_name'],
            'family_name' => $payload['family_name'],
            'picture' => $payload['picture']
        );

        // Kullanıcıyı oluştur veya güncelle
        if ($user->createOrUpdateWithGoogle($googleData)) {
            // Kullanıcı bilgilerini al
            $user->getByGoogleId($googleData['id']);

            // JWT token oluştur
            $token = array(
                "iss" => getenv('APP_URL'),
                "aud" => getenv('APP_URL'),
                "iat" => time(),
                "exp" => time() + getenv('JWT_EXPIRE'),
                "data" => array(
                    "id" => $user->id,
                    "email" => $user->email,
                    "role" => $user->role
                )
            );

            $jwt = JWT::encode($token, getenv('JWT_SECRET'), 'HS256');

            // Yanıt döndür
            http_response_code(200);
            echo json_encode(array(
                "message" => "Giriş başarılı.",
                "token" => $jwt,
                "user" => array(
                    "id" => $user->id,
                    "firstName" => $user->firstName,
                    "lastName" => $user->lastName,
                    "email" => $user->email,
                    "role" => $user->role,
                    "avatar" => $user->avatar
                )
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Kullanıcı kaydedilirken hata oluştu."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Geçersiz Google token."));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Hata: " . $e->getMessage()));
}
