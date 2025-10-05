<?php
class HomeController {
    public function index() {
        $title = "Accueil";
        $body = __DIR__ . '/../views/home/index.php';
        $navbar = __DIR__ . '/../views/shared/navbar.php';
        $footer = __DIR__ . '/../views/shared/footer.php';
        include __DIR__ . '/../views/shared/layout.php';
    }

    public function dashboard() {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        } else {
            $title = "Tableau de bord";
            include __DIR__ . '/../views/dashboard/dashboard.php';
        }
    }
}
