<?php
/**
 * Fichier : DashboardController.php
 * Rôle : Prépare et affiche la page du tableau de bord.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Services\EnergyCsvService;

/**
 * Contrôleur DashboardController
 * Affiche le tableau de bord principal de l'application.
 *
 * @package App\Controllers
 */
class DashboardController extends Controller {
    private EnergyCsvService $energyService;

    public function __construct() {
        parent::__construct();
        $this->energyService = new EnergyCsvService();
    }

    /**
     * Affiche la page principale du dashboard.
     * Récupère la liste des villes disponibles pour les filtres.
     * Route: GET /dashboard
     * 
     * @return void
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