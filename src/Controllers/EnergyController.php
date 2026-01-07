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
        $type = $_GET['type'] ?? 'all';
        $city = $_GET['city'] ?? 'Lyon';
        $compare = !empty($_GET['compare']) ? $_GET['compare'] : null;
        
        $from = $_GET['from'] ?? date('Y-m-01');
        // Si pas de date de fin, on prend la même que le début
        $to = !empty($_GET['to']) ? $_GET['to'] : $from;

        try {
            $model = new EnergyModel();
            
            // ÉTAPE 1 : Récupérer les données RÉELLES (CSV)
            $realData = $model->getEnergyData($type, $city, $from, $to, $compare);
            $finalData = $realData['data']; // On commence avec ce qu'on a

            // ÉTAPE 2 : Détecter le "Trou" à la fin
            // Quelle est la dernière date qu'on a trouvée dans le CSV ?
            $lastDateFound = $from; // Par défaut, le début

            if (!empty($finalData)) {
                // On prend la date du dernier point de données
                // (Supposons que les données sont triées chronologiquement)
                $lastItem = end($finalData);
                // On coupe pour n'avoir que YYYY-MM-DD
                $lastDateFound = substr($lastItem['date'], 0, 10);
            } else {
                // Si le CSV est vide mais qu'on demande une période, 
                // on considère que le "trou" commence dès le début, moins 1 jour
                $lastDateFound = date('Y-m-d', strtotime($from . ' -1 day'));
            }

            // ÉTAPE 3 : Faut-il combler avec l'IA ?
            // Si la dernière date trouvée est AVANT la date de fin demandée ($to)
            if ($lastDateFound < $to) {
                
                // Le début de la simulation = Lendemain de la dernière donnée réelle
                $simStart = date('Y-m-d', strtotime($lastDateFound . ' +1 day'));
                $simEnd = $to;

                // Sécurité : On ne simule pas plus de 3 jours (pour ne pas surcharger l'API)
                // Tu peux augmenter cette limite si tu veux
                $maxSimDate = date('Y-m-d', strtotime($simStart . ' +3 days'));
                if ($simEnd > $maxSimDate) {
                    $simEnd = $maxSimDate; 
                }

                // On lance la simulation seulement si la période est valide
                if ($simStart <= $simEnd) {
                    $targetType = ($type === 'all') ? 'solaire' : $type;
                    
                    // On récupère les données simulées
                    $simulated = $model->simulateDataFromWeather($targetType, $city, $simStart, $simEnd);
                    
                    // FUSION : On ajoute les données simulées à la suite des réelles
                    if (!empty($simulated['data'])) {
                        // array_merge fusionne les deux tableaux
                        $finalData = array_merge($finalData, $simulated['data']);
                    }
                }
            }
            
            // On renvoie le tout fusionné
            $response = $realData; // On reprend la structure de base
            $response['data'] = $finalData; // On met les données fusionnées
            $response['to'] = $to; // On confirme la date de fin demandée
            
            \App\Core\JsonResponse::send($response);

        } catch (\Exception $e) {
            \App\Core\JsonResponse::error($e->getMessage(), 500);
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

            $finfo = \finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = \finfo_file($finfo, $file['tmp_name']);

            $allowedMimes = [
                'text/csv',
                'text/plain',
                'application/vnd.ms-excel',
                'application/csv',
                'text/x-csv',
                'application/x-csv',
                'text/x-comma-separated-values',
                'text/comma-separated-values'
            ];

            if (!in_array($mimeType, $allowedMimes)) {
                // Si le type détecté n'est pas dans la liste, on rejette
                $this->flash('error', "Fichier invalide détecté (Type réel : $mimeType). Seuls les vrais CSV sont acceptés.");
                $this->redirect('/dashboard');
                exit; // Sécurité : on arrête tout
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

    /**
     * Action : Supprime le fichier CSV de l'utilisateur.
     * URL : /energy/delete
     */
    public function delete(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user']['id'];
            
            // On reconstruit le chemin exact du fichier unique de l'utilisateur
            // Note : On utilise le même nom que dans la fonction upload()
            $targetPath = __DIR__ . '/../../Storage/energy_user_' . $userId . '.csv';

            if (file_exists($targetPath)) {
                // Suppression physique du fichier
                if (unlink($targetPath)) {
                    $this->flash('success', "Vos données ont été supprimées.");
                } else {
                    $this->flash('error', "Erreur lors de la suppression du fichier.");
                }
            } else {
                $this->flash('error', "Aucun fichier à supprimer.");
            }
        }

        $this->redirect('/dashboard');
    }
}