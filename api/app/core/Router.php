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
        $third = $segments[2] ?? null;
        $fourth = $segments[3] ?? null;

        // Check for exact matches first
        $routeKeyWithAction = $resource . ($second ? "/$second" : '');
        if (isset($this->routes[$method][$routeKeyWithAction])) {
            $controllerAction = explode('@', $this->routes[$method][$routeKeyWithAction]);
            $id = $segments[2] ?? null;
            return [$controllerAction[0], $controllerAction[1], $id];
        }

        // Check for dynamic routes with {id} parameter
        foreach ($this->routes[$method] as $route => $controllerAction) {
            if (strpos($route, '{id}') !== false) {
                $pattern = str_replace('{id}', '([0-9]+)', $route);
                $fullPath = $resource . ($second ? "/$second" : '') . ($third ? "/$third" : '') . ($fourth ? "/$fourth" : '');
                
                if (preg_match('#^' . $pattern . '$#', $fullPath, $matches)) {
                    $controllerActionParts = explode('@', $controllerAction);
                    
                    // Extract the ID from the matched pattern
                    if (count($matches) > 1) {
                        $id = $matches[1]; // First captured group is the ID
                    } else {
                        $id = $second; // Fallback
                    }
                    
                    return [$controllerActionParts[0], $controllerActionParts[1], $id];
                }
            }
        }

        // Fallback to simple resource/id pattern
        if (isset($this->routes[$method][$resource])) {
            $controllerAction = explode('@', $this->routes[$method][$resource]);
            $id = $second;
            return [$controllerAction[0], $controllerAction[1], $id];
        }

        return null;
    }
}
