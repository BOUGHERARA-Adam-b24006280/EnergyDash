<?php
/**
 * Fichier : index.php
 * Rôle : Point d'entrée unique de l'application (Front Controller).
 * Initialise la session, charge l'autoloader et dispatche la requête via le routeur.
 */

// Stock dans la mémoire tampon (Output Buffering) au lieu de l'envoyer directement
ob_start(); 

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Config/config.php';
require_once __DIR__ . '/../src/Config/routes.php';


// Configuration sécurisée de la session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',                       // Utilise le domaine courant
        'secure'   => isset($_SERVER['HTTPS']), // Sécurisé si HTTPS
        'httponly' => true,                     // Empêche l'accès via JS
        'samesite' => 'Strict'                  // Protection CSRF
    ]);
}

// Transforme les erreurs en Exception
set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// Routage des requêtes
/** @var \App\Core\Router $router Indication pour PHPStan */
try {
    $router->dispatch();
    ob_end_flush(); // Envoie le tampon
} catch (Throwable $e) {
    // Evite l'affiche d'une page incomplète
    if (ob_get_length()) {
        ob_end_clean(); // Nettoyage tampon en cas d'erreur
    }

    $errorController = new App\Controllers\ErrorController();
    $errorController->error500page();
}