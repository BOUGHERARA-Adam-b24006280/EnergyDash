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
    public function add(string $method, string $uri, array $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'uri'    => $uri,
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
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD']; // Récupère la méthode HTTP réelle
        $method = strtoupper($method); // Met en majuscules pour uniformité

        $uri = $_SERVER['REQUEST_URI']; // Récupère l'URI réelle
        $uri = parse_url($uri, PHP_URL_PATH); // Extrait le chemin sans les paramètres de requête
        
        if ($uri !== '/') {
            $uri = rtrim($uri, '/'); // Supprime le slash final si l'URI ne vaut pas '/' (pas d'accueil)
        }
        
        foreach ($this->routes as $route) {
            
            if ($route['method'] === $method && $route['uri'] === $uri) {
                [$controller, $action] = $route['action'];

                $controllerInstance = new $controller();
                $controllerInstance->$action();

                return;
            }
        }

        http_response_code(404);
        $title = "Page non trouvée";
        require __DIR__ . '/../Views/shared/header.php';
        require __DIR__ . '/../Views/error/404.php';
        require __DIR__ . '/../Views/shared/footer.php';
    }
}