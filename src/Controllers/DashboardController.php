<?php
/**
 * Fichier : DashboardController.php
 * Rôle : Affiche le tableau de bord et charge la liste des villes.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Services\EnergyCsvService;

class DashboardController extends Controller {
    private EnergyCsvService $energyService;

    public function __construct() {
        parent::__construct();
        $this->energyService = new EnergyCsvService();
    }

    /**
     * Route: GET /dashboard
     */
    public function index(): void {
        $this->requireLogin();

        $cities = $this->energyService->getAvailableCities();

        $this->render('dashboard/dashboard', [
            'title'  => 'Tableau de bord - EnergyDash',
            'cities' => $cities
        ]);
    }
}