<?php

namespace controllers;

class HomeController
{

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