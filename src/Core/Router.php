<?php
/**
 * Fichier : Router.php
 * Rôle : Gère la logique de routage des requêtes HTTP
 */

namespace App\Core;

/**
 * Routeur basique gérant des routes GET/POST et leur dispatch.
 */
class Router {
    /**
     * Liste des routes enregistrées.
     *
     * @var array<int, array{
     *     method: string,
     *     uri: string,
     *     action: array{0: class-string, 1: string}
     * }>
     */
    private array $routes = [];

    /**
     * Ajoute une route à la liste.
     *
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $uri URI de la route
     * @param array{0: class-string, 1: string} $action Contrôleur et méthode associés
     */
    public function add(string $method, string $uri, array $action): void{
        $this->routes[] = [
            'method' => $method,
            'uri'    => $uri,
            'action' => $action,
        ];
    }

    /**
     * Cherche la route correspondante et exécute son action.
     *
     * Cette méthode analyse l'URI de la requête et la méthode HTTP pour trouver
     * une route correspondante enregistrée. Si trouvée, elle instancie le contrôleur
     * et appelle la méthode associée. Sinon, elle affiche une page 404.
     *
     * @return void
     * @throws \LogicException Si le contrôleur ou la méthode sont introuvables.
     */
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET'; // Récupère la méthode HTTP réelle
        if (!is_string($method)) {
            $method = 'GET';
        }
        $method = strtoupper($method); // Met en majuscules pour uniformité

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/'; // Récupère l'URI réelle
        if (!is_string($requestUri)) {
            $requestUri = '/';
        }
        $uriPath = parse_url($requestUri, PHP_URL_PATH); // Extrait le chemin sans les paramètres de requête
        if (!is_string($uriPath)) {
            $uriPath = '/';
        }
        
        if ($uriPath !== '/') {
            $uriPath = rtrim($uriPath, '/'); // Supprime le slash final si l'URI ne vaut pas '/' (page d'accueil)
        }
        
        foreach ($this->routes as $route) {
            
            if ($route['method'] === $method && $route['uri'] === $uriPath) {
                [$controller, $action] = $route['action'];
                if (!class_exists($controller)) {
                    throw new \LogicException("Unable to load class: $controller");
                }
                $controller = new $controller();

                if (!is_callable([$controller, $action])) {
                    throw new \LogicException("Method not callable or not found: $action");
                }
                $controller->$action();

                return;
            }
        }

        $errorController = new \App\Controllers\ErrorController();
        $errorController->error404page();
    }
}