<?php

// La classe sert à traiter les requêtes
class RequestController
{
    public function TreatRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $this->POST_Request();
        }
    }
    public function POST_Request()
    {
        // Traite les requêtes POST
        switch ($_POST['action']) {
        case 'switchTheme':
            require_once "controllers/HomeController.php";
            (new HomeController())->switchTheme();
            exit;
    }
    }
}