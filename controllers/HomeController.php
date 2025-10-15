<?php
// HomeController.php
class HomeController {
    public function index() {
        require __DIR__ . '/../views/home/index.php';
    }
}

// DashboardController.php
class DashboardController {
    public function index() {
        require __DIR__ . '/../views/dashboard/index.php';
    }
}
?>