<?php

namespace controllers;

class HomeController
{
    public function switchTheme()
    {
        if (!isset($theme)){
            $theme = "light";
            $toggle = "assets/img/Moon."
        }
        elseif ($theme == "dark"){
            $theme = "light";
        }
        else {
            $theme = "dark";
        }
    }
}