<?php

namespace Middleware;

class SecurityHeadersMiddleware {
    public function handle($request, $next) {
        // XSS koruması
        header('X-XSS-Protection: 1; mode=block');
        
        // Clickjacking koruması
        header('X-Frame-Options: DENY');
        
        // MIME type sniffing koruması
        header('X-Content-Type-Options: nosniff');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $cspHeader = "default-src 'self'; " .
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://apis.google.com; " .
                    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
                    "img-src 'self' data: https:; " .
                    "font-src 'self' https://fonts.gstatic.com; " .
                    "connect-src 'self' https://apis.google.com; " .
                    "frame-src 'self' https://accounts.google.com; " .
                    "object-src 'none'";
        
        header("Content-Security-Policy: " . $cspHeader);
        
        // HSTS (sadece HTTPS kullanımı)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Feature Policy
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

        return $next($request);
    }
}
