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
    public function index(): void
    {
        // 1. Vérifie si l'utilisateur est connecté
        $this->requireLogin();
        
        // 2. Instancie le modèle pour lire le CSV
        $model = new EnergyModel();
        
        // 3. Récupère la liste des villes pour le menu déroulant
        // (Assurez-vous que la méthode getAvailableCities existe bien dans EnergyModel)
        $cities = $model->getAvailableCities();

        // 4. Affiche la vue en lui passant les variables
        // Le tableau associatif permet d'utiliser $title et $cities directement dans la vue
        $this->render('dashboard/dashboard', [
            'title'  => 'Tableau de bord - EnergyDash',
            'cities' => $cities
        ]);
    }
}