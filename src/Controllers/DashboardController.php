<?php
/**
 * Fichier : DashboardController.php
 * Rôle : Affiche le tableau de bord et charge la liste des villes.
 */

namespace App\Controllers;

/**
 * Classe DashboardController
 * Gère la vue principale de l'application où sont affichés les graphiques et les données.
 * Fait le lien entre le service de données (EnergyCsvService) et la vue.
 *
 * @package App\Controllers
 */
class DashboardController extends \App\Core\Controller {

    /** @var EnergyCsvService Service responsable de la lecture du CSV et des prévisions */
    private \App\Services\EnergyCsvService $energyService;

    /**
     * Constructeur.
     * Initialise la session (via le parent) et instancie le service de données.
     */
    public function __construct() {
        parent::__construct();
        $this->energyService = new \App\Services\EnergyCsvService();
    }

    /**
     * Affiche la page du tableau de bord.
     * Route: GET /dashboard
     *
     * @return void
     */
    public function index(): void {
        $this->requireLogin();

        $this->initCsrf();

        $cities = $this->energyService->getAvailableCities();
        $energyMapping = $this->energyService->getCityEnergyMapping();

        $this->view->render('dashboard/dashboard', [
            'title'  => 'Tableau de bord - EnergyDash',
            'cities' => $cities,
            'energyMapping' => $energyMapping,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }
}