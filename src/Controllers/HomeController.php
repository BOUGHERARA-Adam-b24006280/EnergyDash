<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
        $this->render('shared/layout', [
            'title'   => 'Accueil',
            'message' => 'Hello depuis HomeController',
            'content' => 'home/index'
        ]);
    }
}