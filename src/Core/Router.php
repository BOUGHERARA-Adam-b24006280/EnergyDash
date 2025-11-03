<?php
/**
 * Fichier : Router.php
 * Rôle : Gère la logique de routage des requêtes HTTP
 * Auteur : Mohamed-Amine HADDAD
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
    public function dispatch(string $method, string $uri): void {
        $cleanUri = parse_url($uri, PHP_URL_PATH);
        $cleanUri = rtrim((string) parse_url($uri, PHP_URL_PATH), '/') ?: '/';
        
        foreach ($this->routes as $route) {
            $path = rtrim($route['path'], '/') ?: '/';
            if ($route['method'] === strtoupper($method) && $path === $cleanUri) {
                [$controller, $action] = $route['action'];
                $controllerInstance = new $controller();
                $controllerInstance->$action();
                return;
            }
        }

        http_response_code(404);
        echo "<h1>Erreur 404 : route non trouvée ($cleanUri)</h1>";
    }
}