<?php

namespace HiveStock\Http;

class Router
{
    /** @var array<int, array{method: string, path: string, handler: callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function patch(string $path, callable $handler): void
    {
        $this->add('PATCH', $path, $handler);
    }

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => rtrim($path, '/') ?: '/',
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }

            $params = $this->match($route['path'], $request->path);
            if ($params === null) {
                continue;
            }

            return ($route['handler'])($request, ...$params);
        }

        return Response::jsonError('Route not found', 404, 'route_not_found');
    }

    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        array_shift($matches);

        return array_map('urldecode', $matches);
    }
}
