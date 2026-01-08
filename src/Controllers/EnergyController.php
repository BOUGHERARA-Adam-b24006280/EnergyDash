<?php
/**
 * Fichier : EnergyController.php
 * Rôle : API JSON et Upload CSV.
 * Auteur : Lucas LEPAPE, Adam Bougherara
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Services\EnergyCsvService;
use App\Core\JsonResponse;

/**
 * Contrôleur EnergyController
 * Gère les données énergétiques (API pour graphiques) et l'import de données (CSV).
 *
 * @package App\Controllers
 */
class EnergyController extends Controller
{
    private EnergyCsvService $energyService;

    public function __construct() {
        parent::__construct();
        $this->energyService = new EnergyCsvService();
    }

    /**
     * API : Renvoie les données énergétiques au format JSON pour le graphique.
     * Supporte le filtrage par type, ville, date et la comparaison de ville.
     * Route: GET /api/energy
     * 
     * @return void
     */
    public function index(): void
    {
        // 1. Récupération des filtres
        $type = isset($_GET['type']) ? $this->sanitize($_GET['type']) : 'all';
        $city = isset($_GET['city']) ? $this->sanitize($_GET['city']) : 'all'; 
        $compare = !empty($_GET['compare']) ? $this->sanitize($_GET['compare']) : null;
        
        $from = isset($_GET['from']) ? $this->sanitize($_GET['from']) : date('Y-m-01');
        
        $to = !empty($_GET['to']) ? $this->sanitize($_GET['to']) : $from;

        try {
            // 2. On récupère d'abord les données RÉELLES (CSV) via le Service
            $realData = $this->energyService->getEnergyData($type, $city, $from, $to, $compare);
            $finalData = $realData['data']; // Les données brutes

            // --- TA LOGIQUE HYBRIDE (Le retour de l'IA) ---
            
            // On cherche la dernière date connue dans le CSV
            $lastDateFound = $from; 
            if (!empty($finalData)) {
                $lastItem = end($finalData);
                $lastDateFound = substr($lastItem['date'], 0, 10);
            } else {
                // Si CSV vide, on simule depuis le début - 1 jour
                $lastDateFound = date('Y-m-d', strtotime($from . ' -1 day'));
            }

            // Si l'utilisateur demande une date plus loin que le CSV, on lance l'IA
            if ($lastDateFound < $to) {
                $simStart = date('Y-m-d', strtotime($lastDateFound . ' +1 day'));
                $simEnd = $to;
                
                // Sécurité : Max 3 jours de prévision pour ne pas surcharger l'API
                $maxSimDate = date('Y-m-d', strtotime($simStart . ' +3 days'));
                if ($simEnd > $maxSimDate) $simEnd = $maxSimDate; 

                if ($simStart <= $simEnd) {
                    // On détermine le type principal pour la simulation
                    $targetType = ($type === 'all') ? 'solaire' : $type;
                    
                    // APPEL DU SERVICE pour la prévision (la fonction que tu as ajoutée tout à l'heure)
                    $simulated = $this->energyService->simulateDataFromWeather($targetType, $city, $simStart, $simEnd);
                    
                    // Fusion : CSV + IA
                    if (!empty($simulated['data'])) {
                        $finalData = array_merge($finalData, $simulated['data']);
                    }
                }
            }
            
            // On prépare la réponse finale
            $response = $realData;
            $response['data'] = $finalData;
            
            JsonResponse::send($response);

        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Action : Traite l'upload d'un fichier CSV.
     * Route: POST /energy/upload
     * 
     * @return void
     */
    public function upload(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->flash('error', "Erreur technique lors du transfert.");
                $this->redirect('/dashboard');
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'csv') {
                $this->flash('error', "Format incorrect. Veuillez envoyer un fichier .csv");
                $this->redirect('/dashboard');
            }

            $userId = $_SESSION['user']['id'];
            $targetDir = __DIR__ . '/../../Storage';
            $targetPath = $targetDir . '/energy_user_' . $userId . '.csv';

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $this->flash('success', "Fichier importé avec succès !");
            } else {
                $this->flash('error', "Impossible d'écrire le fichier sur le disque.");
            }
        }
        $this->redirect('/dashboard');
    }

    /**
     * Action : Supprime le fichier CSV de l'utilisateur.
     * Permet de repasser en mode "Prévision pure" (IA).
     * Route: POST /energy/delete
     * 
     * @return void
     */
    public function delete(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user']['id'];
            $targetPath = __DIR__ . '/../../Storage/energy_user_' . $userId . '.csv';

            if (file_exists($targetPath)) {
                if (unlink($targetPath)) {
                    $this->flash('success', "Données supprimées. Mode Prévision activé !");
                } else {
                    $this->flash('error', "Erreur technique lors de la suppression.");
                }
            } else {
                $this->flash('error', "Aucun fichier à supprimer.");
            }
        }
        $this->redirect('/dashboard');
    }
}