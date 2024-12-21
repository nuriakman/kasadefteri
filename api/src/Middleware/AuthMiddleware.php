<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthMiddleware {
    public static function validateToken() {
        $headers = getallheaders();
        
        // Authorization header kontrolü
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Token bulunamadı']);
            exit();
        }

        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            // Token'ı doğrula
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            return $decoded->data;
        } catch(Exception $e) {
            http_response_code(401);
            echo json_encode(['message' => 'Geçersiz token']);
            exit();
        }
    }

    public static function validateAdmin() {
        $userData = self::validateToken();
        
        if ($userData->role !== 'admin' && $userData->role !== 'superadmin') {
            http_response_code(403);
            echo json_encode(['message' => 'Bu işlem için yetkiniz yok']);
            exit();
        }

        return $userData;
    }

    public static function validateSuperAdmin() {
        $userData = self::validateToken();
        
        if ($userData->role !== 'superadmin') {
            http_response_code(403);
            echo json_encode(['message' => 'Bu işlem için yetkiniz yok']);
            exit();
        }

        return $userData;
    }
}
