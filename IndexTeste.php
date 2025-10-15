<?php

require_once "controllers/HomeController.php";
$controller = new HomeController();
$controller->index();

// Gérer POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'switchTheme') {
        $controller->switchTheme();
        exit; // Important pour que la redirection fonctionne
    }
}