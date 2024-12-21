<?php

namespace Middleware;

class CsrfMiddleware {
    public function handle($request, $next) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || 
            $_SERVER['REQUEST_METHOD'] === 'PUT' || 
            $_SERVER['REQUEST_METHOD'] === 'DELETE') {
            
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            $sessionToken = $_SESSION['csrf_token'] ?? null;

            if (!$csrfToken || !$sessionToken || $csrfToken !== $sessionToken) {
                http_response_code(403);
                echo json_encode(['error' => 'CSRF token doğrulaması başarısız']);
                exit();
            }
        }

        return $next($request);
    }

    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
