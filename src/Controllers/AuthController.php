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

require_once __DIR__ . '/../models/UserModel.php';

class AuthController extends Controller {
    public function login() {
        $header = new Header("EnergyDash");
        $footer = new Footer();

        $layout = new Layout($header, $footer);

        $viewPath = __DIR__ . "/../views/auth/login.php";

        $layout->render($viewPath, "Connexion");
    }

    public function register() {
        $header = new Header("EnergyDash");
        $footer = new Footer();
        $layout = new Layout($header, $footer);

        $viewPath = __DIR__ . "/../views/auth/register.php";
        $layout->render($viewPath, "Inscription");
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login');
    }
}