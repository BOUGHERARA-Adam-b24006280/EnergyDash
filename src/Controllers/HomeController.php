<?php
namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $data = [
            'title' => 'Accueil',
            'message' => 'Bienvenue sur EnergyDash',
        ];

        $this->render('home/index', $data);
    }
}