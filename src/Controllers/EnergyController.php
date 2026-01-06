<?php
/**
 * Fichier : EnergyController.php
 * Rôle : API JSON et Upload CSV.
 * Auteur : Lucas LEPAPE, Adam Bougherara
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EnergyModel;
use App\Core\JsonResponse;

class EnergyController extends Controller
{
    /**
     * API : Renvoie les données JSON filtrées au graphique.
     * URL : /api/energy
     */
    public function index(): void
    {
        // Récupération des filtres
        $type = $_GET['type'] ?? 'all';
        $city = $_GET['city'] ?? 'all'; 
        // Nouveau paramètre pour la comparaison
        $compare = !empty($_GET['compare']) ? $_GET['compare'] : null;
        
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        try {
            $model = new EnergyModel();
            // On passe $compare au modèle
            $data = $model->getEnergyData($type, $city, $from, $to, $compare);
            
            JsonResponse::send($data);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Action : Traite le formulaire d'upload.
     * URL : /energy/upload
     */
    public function upload(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file'];

            // 1. Vérification d'erreur basique
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->flash('error', "Erreur technique lors du transfert (Code " . $file['error'] . ")");
                $this->redirect('/dashboard');
            }

            // 2. Vérification de l'extension
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'csv') {
                $this->flash('error', "Format incorrect. Veuillez envoyer un fichier .csv");
                $this->redirect('/dashboard');
            }

            // 3. Déplacement du fichier vers le dossier Storage
            $userId = $_SESSION['user']['id'];
            
            // Chemin absolu vers le dossier Storage (validé par votre Debug)
            $targetDir = __DIR__ . '/../../Storage';
            $targetPath = $targetDir . '/energy_user_' . $userId . '.csv';

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $this->flash('success', "Fichier importé avec succès !");
            } else {
                $this->flash('error', "Impossible d'écrire le fichier sur le disque.");
            }
        }
        
        // Retour au dashboard
        $this->redirect('/dashboard');
    }
}