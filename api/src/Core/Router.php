<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private ?string $prefix = null;

    /**
     * Yeni bir route grubu oluşturur
     */
    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $this->prefix = $previousPrefix ? $previousPrefix . $prefix : $prefix;
        
        $callback($this);
        
        $this->prefix = $previousPrefix;
    }

    /**
     * GET isteği için route tanımlar
     */
    public function get(string $path, array $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * POST isteği için route tanımlar
     */
    public function post(string $path, array $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * PUT isteği için route tanımlar
     */
    public function put(string $path, array $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    /**
     * DELETE isteği için route tanımlar
     */
    public function delete(string $path, array $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    /**
     * Route'u routes dizisine ekler
     */
    private function addRoute(string $method, string $path, array $handler, array $middlewares): void
    {
        $path = $this->prefix ? $this->prefix . $path : $path;
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'middlewares' => array_merge($this->middlewares, $middlewares)
        ];
    }

    /**
     * Global middleware ekler
     */
    public function middleware(array $middlewares): void
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
    }

    /**
     * İsteği uygun route'a yönlendirir
     */
    public function dispatch(string $method, string $uri)
    {
        if (!isset($this->routes[$method])) {
            throw new \Exception('Method not allowed', 405);
        }

        foreach ($this->routes[$method] as $path => $route) {
            $pattern = $this->convertPathToRegex($path);
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // İlk eşleşmeyi kaldır
                
                // Middleware'leri çalıştır
                foreach ($route['middlewares'] as $middleware) {
                    $middleware->handle([], function($req) {
                        return $req;
                    });
                }
                
                // Controller ve metodu çalıştır
                [$controller, $method] = $route['handler'];
                return call_user_func_array([new $controller(), $method], $matches);
            }
        }

        throw new \Exception('Route not found', 404);
    }

    /**
     * Route path'ini regex'e çevirir
     */
    private function convertPathToRegex(string $path): string
    {
        return '#^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path) . '$#';
    }
}
