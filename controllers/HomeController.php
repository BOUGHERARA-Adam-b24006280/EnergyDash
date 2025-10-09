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
            header('Location: /login');
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

        header('Location: /login');
        exit;
    }

    public function mentionsLegales() {
        $title  = "Mentions légales";
        $body   = __DIR__ . '/../views/legal/mentions.php';
        $navbar = __DIR__ . '/../views/shared/navbar.php';
        $footer = __DIR__ . '/../views/shared/footer.php';
        include __DIR__ . '/../views/shared/layout.php';
    }

    public function siteMap() {
        //header('Content-Type: application/xml; charset=utf-8');
        $title  = "Plan du Site";
        $body   = __DIR__ . '/../views/home/siteMaps.php';
        $navbar = __DIR__ . '/../views/shared/navbar.php';
        $footer = __DIR__ . '/../views/shared/footer.php';
        include __DIR__ . '/../views/shared/layout.php';
    }
}
?>
