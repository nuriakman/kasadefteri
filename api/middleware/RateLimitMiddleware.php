<?php

namespace Middleware;

class RateLimitMiddleware {
    private $redis;
    private $maxRequests;
    private $timeWindow;

    public function __construct($maxRequests = 60, $timeWindow = 60) {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
        $this->redis = new \Redis();
        $this->redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
    }

    public function handle($request, $next) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit:$ip";

        // İstek sayısını kontrol et
        $requests = $this->redis->get($key);
        if ($requests === false) {
            // İlk istek
            $this->redis->setex($key, $this->timeWindow, 1);
        } else if ($requests >= $this->maxRequests) {
            // Limit aşıldı
            http_response_code(429);
            echo json_encode(['error' => 'Çok fazla istek gönderdiniz. Lütfen bekleyin.']);
            exit();
        } else {
            // İstek sayısını artır
            $this->redis->incr($key);
        }

        return $next($request);
    }
}
