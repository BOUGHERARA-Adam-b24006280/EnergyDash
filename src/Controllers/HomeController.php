<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
        $this->render('home/index', [
            'title'   => 'Accueil',
            'message' => 'Hello depuis HomeController'
        ]);
    }
}