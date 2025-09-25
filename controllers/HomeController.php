<?php
class HomeController {
    public function index() {
        $title = "Accueil";
        include __DIR__ . '/../views/home/index.php';

    }

    public function dashboard() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }else {
            $title = "Tableau de bord";
            include __DIR__ . '/../views/dashboard/dashboard.php';
        }
    }
}
?>
