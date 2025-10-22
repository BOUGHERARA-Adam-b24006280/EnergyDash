<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère les actions d'authentification (inscription, connexion, déconnexion)
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Views\Shared\Header;
use App\Views\Shared\Footer;
use App\Views\Shared\Layout;
use PDO;

require_once __DIR__ . '/../Models/UserModel.php';

class AuthController extends Controller {
    public function login(): void {
        $header = new Header();
        $footer = new Footer();

        $layout = new Layout($header, $footer);

        $viewPath = __DIR__ . "/../Views/auth/login.php";

        $layout->render($viewPath, "Connexion");
    }

    public function register(): void {
        $header = new Header();
        $footer = new Footer();
        $layout = new Layout($header, $footer);

        $viewPath = __DIR__ . "/../Views/auth/register.php";
        $layout->render($viewPath, "Inscription");
    }

    public function logout(): void {
        session_start();
        session_destroy();
        header('Location: /login');
    }
}