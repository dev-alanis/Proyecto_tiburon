<?php
declare(strict_types=1);

namespace App\Http;

use App\Security\Exceptions\AuthException;
use App\Support\Validator;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void    { $this->add('GET', $path, $handler); }
    public function post(string $path, callable $handler): void   { $this->add('POST', $path, $handler); }
    public function put(string $path, callable $handler): void    { $this->add('PUT', $path, $handler); }
    public function patch(string $path, callable $handler): void  { $this->add('PATCH', $path, $handler); }
    public function delete(string $path, callable $handler): void { $this->add('DELETE', $path, $handler); }

    private function add(string $method, string $path, callable $handler): void
    {
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[] = compact('method', 'pattern', 'handler');
    }

    public function dispatch(): void
    {
        $method = Request::method();
        $path   = Request::path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $path, $matches)) continue;

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            ($route['handler'])($params);
            return;
        }

        Response::error('NOT_FOUND', 'Ruta no encontrada', 404);
    }
}