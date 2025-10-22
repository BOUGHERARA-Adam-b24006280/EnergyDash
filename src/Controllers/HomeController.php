<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Views\Shared\Header;
use App\Views\Shared\Footer;
use App\Views\Shared\Layout;

class HomeController extends Controller {
    public function index(): void {
        $header = new Header();
        $footer = new Footer();

        $layout = new Layout($header, $footer);

        $viewPath = __DIR__ . "/../Views/home/index.php";

        $layout->render($viewPath, "Accueil");
    }

    public function switchTheme(): void
    {
        // Récupérer le thème actuel
        $currentTheme = $_COOKIE['theme'] ?? 'light';

        // Inverser le thème
        if ($currentTheme === 'dark') {
            $newTheme = 'light';
            $newIcon = 'assets/images/Moon.svg';
        } else {
            $newTheme = 'dark';
            $newIcon = 'assets/images/Sun.svg';
        }

        // Sauvegarder les cookies pour 30 jours
        $expire = time() + (30 * 24 * 60 * 60);
        setcookie('theme', $newTheme, $expire, '/');
        setcookie('toggleTheme', $newIcon, $expire, '/');

        // Rediriger vers la page précédente
        if (isset($_SERVER['HTTP_REFERER']) && is_string($_SERVER['HTTP_REFERER'])) {
            // Si oui, on la met dans $referer
            $referer = $_SERVER['HTTP_REFERER'];
        } else {
            // Sinon, on redirige par défaut vers la page d'accueil
            $referer = 'index.php';
        }
        header('Location: ' . $referer);
        exit; // Toujours exit après un header redirect
    }
}