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

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $title = "Tableau de bord";
        include __DIR__ . '/../views/dashboard/dashboard.php';
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        session_destroy();

        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
?>
