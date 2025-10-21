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
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function login() {
        session_start();

        $header = new Header("EnergyDash");
        $footer = new Footer();

        $layout = new Layout($header, $footer);

        $viewPath = __DIR__ . "/../Views/auth/login.php";

        $layout->render($viewPath, "Connexion");
    }

    public function register() {
        $header = new Header("EnergyDash");
        $footer = new Footer();
        $layout = new Layout($header, $footer);

        $viewPath = __DIR__ . "/../Views/auth/register.php";
        $layout->render($viewPath, "Inscription");
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login');
    }
}