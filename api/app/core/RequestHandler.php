<?php

class RequestHandler
{
    private $pdo;
    private $router;

    public function __construct(PDO $pdo, Router $router)
    {
        $this->pdo = $pdo;
        $this->router = $router;
    }

    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace('/netsentinel/api/', '', $uri);

        $resolved = $this->router->resolve($method, $uri);

        if ($resolved === null) {
            http_response_code(404);
            echo json_encode(["error" => "Route couldn't found"]);
            return;
        }

        [$controllerName, $methodName, $id] = $resolved;
        require_once __DIR__ . '/../controllers/' . $controllerName . '.php';
        
        $controller = new $controllerName($this->pdo);

        if (in_array($method, ['POST', 'PUT'])) {
            $input = json_decode(file_get_contents("php://input"), true);
        } else {
            $input = [];
        }

        if ($id !== null) {
            if (!is_array($input)) {
                $input = [];
            }
            $response = $controller->$methodName($id, $input);
        } else {
            $response = $controller->$methodName($input);
        }

        echo is_array($response) ? json_encode($response) : $response;
    }
}
