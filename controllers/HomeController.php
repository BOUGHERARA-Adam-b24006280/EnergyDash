<?php
// HomeController.php
class HomeController {
    public function index() {
        setcookie("theme", "light", 0, "/");
        setcookie("toggleTheme", "assets/images/Moon.svg", 0, "/");
        include 'views/shared/Layout.php';
    }

    public function switchTheme()
    {
        if ($_COOKIE['theme'] == "dark"){
            setcookie("theme", "light", 0, "/");
            setcookie("toggleTheme", "assets/images/Moon.svg", 0, "/");
        }
        else {
            setcookie("theme", "dark", 0, "/");
            setcookie("toggleTheme", "assets/images/Sun.svg", 0, "/");
        }
    }
}