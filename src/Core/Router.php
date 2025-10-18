<?php
namespace App\Core;

class Router
{
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(string $url): void
    {
        $url = trim($url, '/');

        if (isset($this->routes[$url])) {
            [$controllerName, $method] = $this->routes[$url];
        } else {
            [$controllerName, $method] = $this->routes['404'];
        }

        $controllerClass = "App\\Controllers\\$controllerName";

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            echo "Contrôleur introuvable : $controllerClass";
            exit;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(404);
            echo "Méthode introuvable : $method";
            exit;
        }

        $controller->$method();
    }
}