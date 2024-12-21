<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthMiddleware {
    public function handle($request, $next) {
        // CORS kontrolü
        header('Access-Control-Allow-Origin: ' . $_ENV['FRONTEND_URL']);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');

        // OPTIONS isteğini hemen yanıtla
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        // Public rotalar için kontrol yok
        $publicRoutes = [
            '/auth/login',
            '/auth/google',
            '/auth/refresh'
        ];

        if (in_array($_SERVER['REQUEST_URI'], $publicRoutes)) {
            return $next($request);
        }

        // Token kontrolü
        $token = $this->getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Yetkisiz erişim']);
            exit();
        }

        try {
            // Token doğrulama
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $request['user'] = $decoded;

            // Token yenileme kontrolü
            $now = time();
            if ($decoded->exp - $now < 300) { // 5 dakikadan az kaldıysa
                $newToken = $this->refreshToken($decoded);
                header('X-New-Token: ' . $newToken);
            }

            return $next($request);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Geçersiz token']);
            exit();
        }
    }

    private function getBearerToken() {
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function refreshToken($user) {
        $payload = [
            'sub' => $user->sub,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => time(),
            'exp' => time() + (60 * 60) // 1 saat
        ];

        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }
}
