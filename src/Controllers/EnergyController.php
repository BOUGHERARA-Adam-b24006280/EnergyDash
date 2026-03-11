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
            // ... (Initialisation des services identique à votre code) ...
            $userId = $_SESSION['user']['id'] ?? null;
            $idSuffix = $userId ? (int)$userId : 'default';
            $userFile = __DIR__ . '/../../Storage/energy_user_' . $idSuffix . '.csv';
            $defaultFile = __DIR__ . '/../../Storage/energyData.csv';
            $csvPath = ($userId && file_exists($userFile)) ? $userFile : $defaultFile;

            $csvReader = new CsvReader($csvPath);
            $energyRepository = new EnergyRepository($csvReader);
            $analyticsService = new EnergyAnalyticsService($energyRepository);
            $predictionAlgorithm = PredictionFactory::make($energyRepository, $analyticsService);

            // 1. Récupérer les données réelles
            $rawData = $energyRepository->getEnergyData($type, $city, $from, $to, $compare);
            $finalData = $rawData;
            
            $isBacktest = ($_GET['backtest'] ?? 'false') === 'true';
            $isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');

            // 2. Correction : Gérer la simulation pour le Backtest
            if ($isAdmin && $isBacktest) {
                $targetType = ($type === 'all') ? 'solaire' : $type;
                
                // On doit simuler pour la ville principale ET la ville de comparaison
                $citiesToPredict = [$city];
                if ($compare) $citiesToPredict[] = $compare;

                foreach ($citiesToPredict as $currentCity) {
                    if ($currentCity === 'all') continue; // L'algo ne gère pas 'all' directement

                    // Pour le backtest admin, on veut idéalement les deux algos pour comparer
                    $algos = ['standard', 'lstm'];
                    foreach ($algos as $algoName) {
                        // Forcer l'algo pour cette simulation
                        $tempAlgo = ($algoName === 'lstm') 
                            ? new \App\Strategies\DeepLearningStrategy($energyRepository)
                            : new \App\Services\PredictionService($analyticsService);
                        
                        $sim = $tempAlgo->predict($targetType, $currentCity, $from, $to);
                        
                        // On ajoute manuellement la clé 'algo' pour le JS
                        foreach ($sim['data'] as &$point) {
                            $point['algo'] = $algoName;
                        }
                        $finalData = array_merge($finalData, $sim['data']);
                    }
                }
            }
            else {
                // Mode normal (Prédiction pour le futur uniquement)
                $lastDateFound = empty($finalData) ? date('Y-m-d', strtotime($from . ' -1 day')) : substr(end($finalData)['date'], 0, 10);

                if ($lastDateFound < $to && $city !== 'all') {
                    $simStart = date('Y-m-d', strtotime($lastDateFound . ' +1 day'));
                    $targetType = ($type === 'all') ? 'solaire' : $type;
                    
                    $simulated = $predictionAlgorithm->predict($targetType, $city, $simStart, $to);
                    $finalData = array_merge($finalData, $simulated['data']);
                }
            }
            
            \App\Core\JsonResponse::send([
                'type' => $type,
                'city' => $city,
                'compare' => $compare,
                'from' => $from,
                'to'   => $to,
                'data' => $finalData
            ]);

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
                
                // 1. On cherche bien $_POST['algo'] (comme dans votre HTML)
                $algo = $_POST['algo'] ?? 'standard';
                
                // 2. On autorise 'lstm' au lieu de 'deep_learning'
                $allowedAlgos = ['standard', 'lstm'];
                
                if (in_array($algo, $allowedAlgos)) {
                    $baseDir = realpath(__DIR__ . '/../../');
                    $file = $baseDir ? $baseDir . '/Storage/active_algo.txt' : __DIR__ . '/../../Storage/active_algo.txt';
                    
                    $result = @file_put_contents($file, $algo);
                    
                    if ($result === false) {
                        throw new \Exception("Erreur de permission : Impossible d'écrire dans le fichier active_algo.txt");
                    }
                    
                    $this->flash('success', "L'algorithme de prédiction a été mis à jour avec succès !");
                } else {
                    $this->flash('error', "Algorithme non reconnu.");
                }
            } catch (\Exception $e) {
                $this->flash('error', $e->getMessage());
            }
        }
        
        // On redirige bien vers le dashboard
        $this->redirect('/dashboard');
    }
}