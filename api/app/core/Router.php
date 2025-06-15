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
        $second = $segments[1] ?? null;

        $routeKeyWithAction = $resource . ($second ? "/$second" : '');
        if (isset($this->routes[$method][$routeKeyWithAction])) {
            $controllerAction = explode('@', $this->routes[$method][$routeKeyWithAction]);
            $id = $segments[2] ?? null;
            return [$controllerAction[0], $controllerAction[1], $id];
        }

        if (isset($this->routes[$method][$resource])) {
            $controllerAction = explode('@', $this->routes[$method][$resource]);
            $id = $second;
            return [$controllerAction[0], $controllerAction[1], $id];
        }

        return null;
    }
}
