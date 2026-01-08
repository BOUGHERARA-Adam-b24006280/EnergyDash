<?php
/**
 * Fichier : index.php
 * Rôle : Point d'entrée unique de l'application (Front Controller).
 * Initialise la session, charge l'autoloader et dispatche la requête via le routeur.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Config/config.php';

// Configuration sécurisée de la session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', // Utilise le domaine courant
        'secure' => isset($_SERVER['HTTPS']), // Sécurisé si HTTPS
        'httponly' => true, // Empêche l'accès via JS
        'samesite' => 'Strict' // Protection CSRF
    ]);
    // session_start(); // Désactivé ici, géré par le Controller
}

require_once __DIR__ . '/../src/Config/routes.php';

// Transforme les erreurs en Exception
set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

try {
    $router->dispatch();
} catch (Throwable $e) {
    $errorController = new App\Controllers\ErrorController();
    $errorController->error500page();
}
