<?php

class Router
{
    private $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function resolve(string $method, string $path): ?array
    {
        $segments = explode('/', trim($path, '/'));
        $resource = $segments[0] ?? null;
        $action = $segments[1] ?? null;

        $routeKey = $resource . ($action ? "/$action" : '');

        if (isset($this->routes[$method][$routeKey])) {
            $controllerAction = explode('@', $this->routes[$method][$routeKey]);
            $id = $segments[2] ?? null;
            return [$controllerAction[0], $controllerAction[1], $id];
        }

        return null;
    }
}
