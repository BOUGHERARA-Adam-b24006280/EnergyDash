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

    public function switchTheme()
    {
        if (!isset($theme)){
            $theme = "light";
            $toggle = "assets/img/Moon.svg";
        }
        elseif ($theme == "dark"){
            $theme = "light";
            $toggle = "assets/img/Moon.svg";
        }
        else {
            $theme = "dark";
            $toggle = "assets/img/Sun.svg";
        }
        return $theme;
    }
}