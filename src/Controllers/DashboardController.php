<?php
/**
 * Fichier : DashboardController.php
 * Rôle : Prépare et affiche la page du tableau de bord.
 * Auteur : L'équipe EnergyDash
 */

namespace App\Controllers;

// Importation essentielle du contrôleur parent (pour utiliser $this->render et $this->requireLogin)
use App\Core\Controller;

// Importation essentielle du modèle (pour utiliser new EnergyModel)
use App\Models\EnergyModel;

class DashboardController extends Controller
{
    /**
     * Affiche la page principale du dashboard.
     * URL : /dashboard
     */
    public function index(): void {
        $this->requireLogin();

        $energyModel = new EnergyModel();

        $cities = $energyModel->getAvailableCities();

        $this->render('dashboard/dashboard', [
            'title'  => 'Tableau de bord - EnergyDash',
            'cities' => $cities
]);
    }
}