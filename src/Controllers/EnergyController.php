<?php
/**
 * Fichier : EnergyController.php
 * Rôle : API JSON et Upload CSV.
 */

namespace App\Controllers;

/**
 * Contrôleur EnergyController
 * Gère les données énergétiques (API pour graphiques) et l'import de données (CSV).
 *
 * @package App\Controllers
 */
class EnergyController extends \App\Core\Controller {
    private \App\Services\EnergyCsvService $energyService;

    public function __construct() {
        parent::__construct();
        $this->energyService = new \App\Services\EnergyCsvService();
    }

    /**
     * API : Renvoie les données énergétiques au format JSON pour le graphique.
     * Supporte le filtrage par type, ville, date et la comparaison de ville.
     * Route: GET /api/energy
     * 
     * @return void
     */
    public function index(): void {
        // 1. Récupération des filtres
        $rawType = $_GET['type'] ?? null;
        $type = is_string($rawType) ? $this->sanitize($rawType) : 'all';

        $rawCity = $_GET['city'] ?? null;
        $city = is_string($rawCity) ? $this->sanitize($rawCity) : 'all'; 

        $rawCompare = $_GET['compare'] ?? null;
        $compare = (!empty($rawCompare) && is_string($rawCompare)) ? $this->sanitize($rawCompare) : null;
        
        $rawFrom = $_GET['from'] ?? null;
        $from = (is_string($rawFrom)) ? $this->sanitize($rawFrom) : date('Y-m-01');
        
        $rawTo = $_GET['to'] ?? null;
        $to = (!empty($rawTo) && is_string($rawTo)) ? $this->sanitize($rawTo) : $from;

        try {
            // 2. On récupère d'abord les données RÉELLES (CSV)
            $realData = $this->energyService->getEnergyData($type, $city, $from, $to, $compare);
            $finalData = isset($realData['data']) && is_array($realData['data']) ? $realData['data'] : []; 
            
            foreach ($finalData as &$d) { $d['statut'] = 'reel'; } // On marque explicitement la donnée comme réelle

            // NOUVEAU : Mode Backtest pour l'Administrateur
            $isBacktest = ($_GET['backtest'] ?? 'false') === 'true';
            $user = $_SESSION['user'] ?? null;
            $isAdmin = (is_array($user) && in_array($user['role'] ?? '', ['admin', 'editor']));

            if ($isAdmin && $isBacktest) {
                // En mode backtest, on force la prévision sur les MEMES dates que le passé
                $targetType = ($type === 'all') ? 'solaire' : $type;
                
                $simStandard = $this->energyService->simulateDataFromWeather($targetType, $city, $from, $to);
                foreach($simStandard['data'] as &$d) { $d['algo'] = 'standard'; $d['statut'] = 'prevision'; }
                
                $simLSTM = $this->energyService->simulateDataWithLSTM($targetType, $city, $from, $to);
                foreach($simLSTM['data'] as &$d) { $d['algo'] = 'lstm'; $d['statut'] = 'prevision'; }

                $finalData = array_merge($finalData, $simStandard['data'], $simLSTM['data']);
            } 
            else {
                // Mode normal (Prédiction classique du futur uniquement pour le lendemain)
                $lastDateFound = $from; 
                if (!empty($finalData)) {
                    $lastItem = end($finalData);
                    $lastDateFound = substr($lastItem['date'] ?? $from, 0, 10);
                } else {
                    $lastDateFound = date('Y-m-d', (int)strtotime($from . ' -1 day'));
                }

                if ($lastDateFound < $to) {
                    $simStart = date('Y-m-d', (int)strtotime($lastDateFound . ' +1 day'));
                    $simEnd = $to;
                    if ($simEnd > $simStart) $simEnd = $simStart; // Limité à 1 jour

                    if ($simStart <= $simEnd) {
                        $targetType = ($type === 'all') ? 'solaire' : $type;
                        $algoPath = __DIR__ . '/../../Storage/active_algo.txt';
                        $activeAlgo = file_exists($algoPath) ? trim(file_get_contents($algoPath)) : 'standard';
                        $requestedAlgo = $_GET['algo'] ?? $activeAlgo;

                        if ($requestedAlgo === 'lstm') {
                            $simulated = $this->energyService->simulateDataWithLSTM($targetType, $city, $simStart, $simEnd);
                        } else {
                            $simulated = $this->energyService->simulateDataFromWeather($targetType, $city, $simStart, $simEnd);
                        }
                        if (isset($simulated['data']) && is_array($simulated['data'])) {
                            $finalData = array_merge($finalData, $simulated['data']);
                        }
                    }
                }
            }
            
            $response = $realData;
            $response['data'] = $finalData;
            \App\Core\JsonResponse::send($response);

        } catch (\Exception $e) {
            \App\Core\JsonResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Action : Traite l'upload d'un fichier CSV avec sécurité MIME.
     * Route: POST /energy/upload
     */
    public function upload(): void {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            try {
                $this->validateCsrf();
            } catch (\Exception $e) {
                $this->flash('error', $e->getMessage());
                $this->redirect('/dashboard');
                return;
            }

            /** @var array{name: string, type: string, tmp_name: string, error: int, size: int} $file */
            $file = $_FILES['csv_file'];

            $error = (int)$file['error'];
            $name = (string)$file['name'];
            $tmpName = (string)$file['tmp_name'];

            if ($error !== UPLOAD_ERR_OK) {
                 $this->flash('error', "Erreur lors du transfert du fichier (Code: $error)");
                 $this->redirect('/dashboard');
                 return;
            }

            // 1. Erreur technique
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'csv') {
                $this->flash('error', "Format incorrect. Veuillez envoyer un fichier .csv");
                $this->redirect('/dashboard');
                return;
            }

            // 2. Vérification de l'extension
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'csv') {
                $this->flash('error', "Format incorrect. Veuillez envoyer un fichier .csv");
                $this->redirect('/dashboard');
                return;
            }

            // 3. (AJOUT) Vérification de sécurité du contenu réel (MIME Type)
            $finfo = \finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo === false) {
                $this->flash('error', "Erreur interne lors de l'analyse du fichier.");
                $this->redirect('/dashboard');
                return; 
            }

            $mimeType = \finfo_file($finfo, $file['tmp_name']);
            $allowedMimes = [
                'text/csv', 'text/plain', 'application/vnd.ms-excel', 
                'application/csv', 'text/x-csv', 'application/x-csv', 
                'text/x-comma-separated-values', 'text/comma-separated-values'
            ];

            if ($mimeType === false || !in_array($mimeType, $allowedMimes)) {
                $this->flash('error', "Fichier invalide détecté. Seuls les vrais CSV sont acceptés.");
                $this->redirect('/dashboard');
            }

            // 4. Déplacement
            /** @var array{id: int|string}|null $user */
            $user = $_SESSION['user'] ?? null;

            if (!is_array($user)) {
                $this->redirect('/login');
                return;
            }

            $userId = (int)$user['id'];
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
            try {
                $this->validateCsrf();
            } catch (\Exception $e) {
                $this->flash('error', $e->getMessage());
                $this->redirect('/dashboard');
                return;
            }

            /** @var array{id: int|string}|null $user */
            $user = $_SESSION['user'] ?? null;

            if (!is_array($user)) {
                $this->redirect('/login');
                return;
            }
            
            $userId = (int)$user['id'];
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

    /**
     * Action (Admin) : Choisit l'algorithme par défaut pour les utilisateurs.
     * Route: POST /energy/setAlgorithm
     */
    public function setAlgorithm(): void {
        $this->requireLogin();
        $user = $_SESSION['user'] ?? null;
        
        if (!is_array($user) || !in_array($user['role'] ?? '', ['admin', 'editor'])) {
            $this->redirect('/dashboard');
            return;
        }

        $algo = $_POST['algo'] === 'lstm' ? 'lstm' : 'standard';
        file_put_contents(__DIR__ . '/../../Storage/active_algo.txt', $algo);
        
        $this->flash('success', "L'algorithme actif a été mis à jour.");
        $this->redirect('/dashboard');
    }

}