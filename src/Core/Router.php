<?php

namespace App\Core;

use App\Core\Database;

class Router {
    private array $routes = [];
    private \PDO $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function add(string $method, string $path, array $action): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path'   => rtrim($path, '/') ?: '/',
            'action' => $action,
        ];
    }

    public function dispatch(string $uri, string $method): void {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                [$controller, $action] = $route['action'];

                if (!class_exists($controller)) {
                    throw new \Exception("Contrôleur $controller introuvable");
                }

                if (!method_exists($controller, $action)) {
                    throw new \Exception("Méthode $action absente dans $controller");
                }

                $controllerInstance = new $controller($this->db);
                $controllerInstance->$action();
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../Views/error/404.php';
    }
}
