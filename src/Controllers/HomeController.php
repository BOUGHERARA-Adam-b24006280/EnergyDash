<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
        $this->render('shared/layout', [
            'title'   => 'Accueil',
            'message' => 'Hello depuis HomeController',
            'content' => 'home/index'
        ]);
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

        // Sauvegarder les cookies (30 jours)
        $expire = time() + (30 * 24 * 60 * 60);
        setcookie('theme', $newTheme, $expire, '/');
        setcookie('toggleTheme', $newIcon, $expire, '/');

        // ✅ IMPORTANT : Rediriger vers la page précédente
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header('Location: ' . $referer);
        exit; // Toujours exit après un header redirect
    }
}