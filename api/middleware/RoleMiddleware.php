<?php

namespace Middleware;

class RoleMiddleware {
    private $allowedRoles;

    public function __construct($roles) {
        $this->allowedRoles = is_array($roles) ? $roles : [$roles];
    }

    public function handle($request, $next) {
        if (!isset($request['user']) || !isset($request['user']->role)) {
            http_response_code(401);
            echo json_encode(['error' => 'Yetkisiz erişim']);
            exit();
        }

        if (!in_array($request['user']->role, $this->allowedRoles)) {
            http_response_code(403);
            echo json_encode(['error' => 'Bu işlem için yetkiniz yok']);
            exit();
        }

        return $next($request);
    }
}
