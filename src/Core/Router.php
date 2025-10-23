<?php
/**
 * Fichier : Router.php
 * Rôle : Gère la logique de routage des requêtes HTTP
 * Auteur : Mohamed-Amine HADDAH
 */

namespace App\Core;

/**
 * Routeur basique gérant des routes GET/POST et leur dispatch.
 */
class Router
{
    /**
     * Liste des routes enregistrées.
     *
     * @var array<int, array{
     *     method: string,
     *     path: string,
     *     action: array{0: class-string, 1: string}
     * }>
     */
    private array $routes = [];
    private \PDO $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Ajoute une route à la liste.
     *
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $path URI de la route
     * @param array{0: class-string, 1: string} $action Contrôleur et méthode associés
     */
    public function add(string $method, string $path, array $action): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path'   => rtrim($path, '/') ?: '/',
            'action' => $action,
        ];
    }

    /**
     * Cherche la route correspondante et exécute son action.
     *
     * @param string $uri
     * @param string $method
     * @throws \Exception Si le contrôleur ou la méthode sont introuvables.
     */
    public function dispatch(string $uri, string $method): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && $route['path'] === (rtrim($uri, '/') ?: '/')) {
                [$controller, $action] = $route['action'];

                if (!class_exists($controller)) {
                    throw new \Exception("Contrôleur {$controller} introuvable");
                }

                if (!method_exists($controller, $action)) {
                    throw new \Exception("Méthode {$action} absente dans {$controller}");
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
