<?php
/**
 * Fichier : DashboardController.php
 * Rôle : Affiche le tableau de bord et charge la liste des villes.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Services\EnergyCsvService;

/**
 * Classe DashboardController
 * Gère la vue principale de l'application où sont affichés les graphiques et les données.
 * Fait le lien entre le service de données (EnergyCsvService) et la vue.
 *
 * @package App\Controllers
 */
class DashboardController extends Controller {

    /** @var EnergyCsvService Service responsable de la lecture du CSV et des prévisions */
    private EnergyCsvService $energyService;

    /**
     * Constructeur.
     * Initialise la session (via le parent) et instancie le service de données.
     */
    public function __construct() {
        parent::__construct();
        $this->energyService = new EnergyCsvService();
    }

    /**
     * Affiche la page du tableau de bord.
     * Route: GET /dashboard
     *
     * @return void
     */
    public function index(): void {
        $this->requireLogin();

        $cities = $this->energyService->getAvailableCities();

        $this->view->render('dashboard/dashboard', [
            'title'  => 'Tableau de bord - EnergyDash',
            'cities' => $cities
        ]);
    }
}