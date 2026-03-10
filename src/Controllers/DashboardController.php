<?php
/**
 * Fichier : DashboardController.php
 * Rôle : Affiche le tableau de bord et charge la liste des villes.
 */

namespace App\Controllers;

use App\Infrastructure\CsvReader;
use App\Repositories\EnergyRepository;

/**
 * Classe DashboardController
 * Gère la vue principale de l'application où sont affichés les graphiques et les données.
 * Fait le lien entre le dépôt de données (EnergyRepository) et la vue.
 *
 * @package App\Controllers
 */
class DashboardController extends \App\Core\Controller {

    /** @var EnergyRepository Dépôt responsable des requêtes sur les données énergétiques */
    private EnergyRepository $energyRepository;

    /**
     * Constructeur.
     * Initialise la session (via le parent) et instancie l'accès aux données.
     */
    public function __construct() {
        parent::__construct();
        
        $userId = $_SESSION['user']['id'] ?? null;
        $idSuffix = $userId ? (int)$userId : 'default';
        $userFile = __DIR__ . '/../../Storage/energy_user_' . $idSuffix . '.csv';
        $defaultFile = __DIR__ . '/../../Storage/energyData.csv';
        $csvPath = ($userId && file_exists($userFile)) ? $userFile : $defaultFile;

        $csvReader = new \App\Infrastructure\CsvReader($csvPath);
        $this->energyRepository = new \App\Repositories\EnergyRepository($csvReader);
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

        // 3. Utiliser le Repository au lieu de l'ancien CsvService
        $cities = $this->energyRepository->getAvailableCities();
        $energyMapping = $this->energyRepository->getCityEnergyMapping();

        $this->view->render('dashboard/dashboard', [
            'title'  => 'Tableau de bord - EnergyDash',
            'cities' => $cities,
            'energyMapping' => $energyMapping,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }
}