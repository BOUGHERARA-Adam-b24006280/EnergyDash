<?php
namespace App\Controllers;

use App\Infrastructure\CsvReader;
use App\Repositories\EnergyRepository;
use App\Services\EnergyAnalyticsService;
use App\Services\FileUploadService;
use App\Factories\PredictionFactory;

class EnergyController extends \App\Core\Controller {
    
    public function index(): void {
        $type = $this->sanitize($_GET['type'] ?? 'all');
        $city = $this->sanitize($_GET['city'] ?? 'all'); 
        $compare = (!empty($_GET['compare'])) ? $this->sanitize($_GET['compare']) : null;
        $from = $this->sanitize($_GET['from'] ?? date('Y-m-01'));
        $to = (!empty($_GET['to'])) ? $this->sanitize($_GET['to']) : $from;

        try {
            // 1. Déterminer le fichier cible
            $userId = $_SESSION['user']['id'] ?? null;
            $idSuffix = $userId ? (int)$userId : 'default';
            $userFile = __DIR__ . '/../../Storage/energy_user_' . $idSuffix . '.csv';
            $defaultFile = __DIR__ . '/../../Storage/energyData.csv';
            $csvPath = ($userId && file_exists($userFile)) ? $userFile : $defaultFile;

            // 2. Initialiser les nouveaux services
            $csvReader = new CsvReader($csvPath);
            $energyRepository = new EnergyRepository($csvReader);
            $analyticsService = new EnergyAnalyticsService($energyRepository);
            
            // 3. ON UTILISE LA FACTORY ICI !
            // Le contrôleur demande à la fabrique de lui donner le bon algorithme 
            // (Standard ou IA) en fonction de ce qui a été choisi dans les réglages.
            $predictionAlgorithm = PredictionFactory::make($energyRepository, $analyticsService);

            // 4. Récupérer les données brutes depuis le Repository
            $rawData = $energyRepository->getEnergyData($type, $city, $from, $to, $compare);
            
            // 5. Formater la structure de base
            $realData = [
                'type' => $type,
                'city' => $city,
                'from' => $from,
                'to'   => $to,
                'data' => $rawData
            ];
            
            $finalData = $realData['data']; 
            
            // 6. Mode Backtest (Admin)
            $isBacktest = ($_GET['backtest'] ?? 'false') === 'true';
            // Seul l'admin pur a le droit au backtest
            $isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');

            if ($isAdmin && $isBacktest) {
                $targetType = ($type === 'all') ? 'solaire' : $type;
                
                // Appel UNIVERSEL de prédiction (via l'interface)
                $simulated = $predictionAlgorithm->predict($targetType, $city, $from, $to);
                $finalData = array_merge($finalData, $simulated['data']);
            } 
            else {
                // Mode normal (Futur)
                $lastDateFound = empty($finalData) ? date('Y-m-d', strtotime($from . ' -1 day')) : substr(end($finalData)['date'], 0, 10);

                if ($lastDateFound < $to) {
                    $simStart = date('Y-m-d', strtotime($lastDateFound . ' +1 day'));
                    $simEnd = ($to > $simStart) ? $simStart : $to;

                    $targetType = ($type === 'all') ? 'solaire' : $type;
                    
                    // Appel UNIVERSEL de prédiction (via l'interface)
                    $simulated = $predictionAlgorithm->predict($targetType, $city, $simStart, $simEnd);
                    $finalData = array_merge($finalData, $simulated['data']);
                }
            }
            
            $realData['data'] = $finalData;
            \App\Core\JsonResponse::send($realData);

        } catch (\Exception $e) {
            \App\Core\JsonResponse::error($e->getMessage(), 500);
        }
    }

    public function upload(): void {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            try {
                $this->validateCsrf();
                $userId = (int)$_SESSION['user']['id'];
                
                $uploadService = new FileUploadService();
                $uploadService->handleCsvUpload($_FILES['csv_file'], $userId);
                
                $this->flash('success', "Fichier importé avec succès !");
            } catch (\Exception $e) {
                $this->flash('error', $e->getMessage());
            }
        }
        $this->redirect('/dashboard');
    }

    public function delete(): void {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCsrf();
                $userId = (int)$_SESSION['user']['id'];
                
                $uploadService = new FileUploadService();
                if ($uploadService->deleteUserCsv($userId)) {
                    $this->flash('success', "Données supprimées. Mode Prévision activé !");
                } else {
                    $this->flash('error', "Aucun fichier à supprimer ou erreur technique.");
                }
            } catch (\Exception $e) {
                $this->flash('error', $e->getMessage());
            }
        }
        $this->redirect('/dashboard');
    }

    /**
     * Sauvegarde le choix de l'algorithme (Standard ou Deep Learning) 
     * depuis le formulaire d'administration.
     */
    public function setAlgorithm(): void {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCsrf();
                
                // Récupère l'algo choisi dans le menu déroulant
                $algo = $_POST['algorithm'] ?? 'standard';
                
                // Sécurité : On vérifie que le choix est valide
                $allowedAlgos = ['standard', 'deep_learning'];
                
                if (in_array($algo, $allowedAlgos)) {
                    // On écrit le choix physiquement dans le fichier
                    $file = __DIR__ . '/../../Storage/active_algo.txt';
                    file_put_contents($file, $algo);
                    
                    $this->flash('success', "L'algorithme de prédiction a été mis à jour avec succès !");
                } else {
                    $this->flash('error', "Algorithme non reconnu.");
                }
            } catch (\Exception $e) {
                $this->flash('error', $e->getMessage());
            }
        }
        
        $this->redirect('/dashboard');
    }
}