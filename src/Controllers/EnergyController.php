<?php
/**
 * Fichier : EnergyController.php
 * Rôle : Gère les actions liées aux données énergétiques, aux imports et aux algorithmes de prédiction.
 */

namespace App\Controllers;

use App\Infrastructure\CsvReader;
use App\Repositories\EnergyRepository;
use App\Services\EnergyAnalyticsService;
use App\Services\FileUploadService;
use App\Factories\PredictionFactory;

/**
 * Cette classe est responsable de la récupération des données d'énergie,
 * de la gestion des fichiers CSV utilisateurs et de la configuration de l'algorithme de prédiction.
 */
class EnergyController extends \App\Core\Controller {
    
    /**
     * Gère l'affichage et la récupération des données énergétiques (réelles et prévisions).
     * @return void Envoie une réponse JSON via App\Core\JsonResponse.
     */
    public function index(): void {
        $type = $this->sanitize($_GET['type'] ?? 'all');
        $city = $this->sanitize($_GET['city'] ?? 'all'); 
        $compare = (!empty($_GET['compare'])) ? $this->sanitize($_GET['compare']) : null;
        $from = $this->sanitize($_GET['from'] ?? date('Y-m-01'));
        $to = (!empty($_GET['to'])) ? $this->sanitize($_GET['to']) : $from;

        try {
            $userId = $_SESSION['user']['id'] ?? null;
            $idSuffix = $userId ? (int)$userId : 'default';
            $userFile = __DIR__ . '/../../Storage/energy_user_' . $idSuffix . '.csv';
            $defaultFile = __DIR__ . '/../../Storage/energyData.csv';
            $csvPath = ($userId && file_exists($userFile)) ? $userFile : $defaultFile;

            $csvReader = new CsvReader($csvPath);
            $energyRepository = new EnergyRepository($csvReader);
            $analyticsService = new EnergyAnalyticsService($energyRepository);
            $predictionAlgorithm = PredictionFactory::make($energyRepository, $analyticsService);

            $rawData = $energyRepository->getEnergyData($type, $city, $from, $to, $compare);
            $finalData = $rawData;
            
            $isBacktest = ($_GET['backtest'] ?? 'false') === 'true';
            $isAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');

            if ($isAdmin && $isBacktest) {
                $targetType = ($type === 'all') ? 'solaire' : $type;
                
                $citiesToPredict = [$city];
                if ($compare) $citiesToPredict[] = $compare;

                foreach ($citiesToPredict as $currentCity) {
                    if ($currentCity === 'all') continue;

                    $algos = ['standard', 'lstm'];
                    foreach ($algos as $algoName) {
                        $tempAlgo = ($algoName === 'lstm') 
                            ? new \App\Strategies\DeepLearningStrategy($energyRepository)
                            : new \App\Services\PredictionService($analyticsService);
                        
                        $sim = $tempAlgo->predict($targetType, $currentCity, $from, $to);
                        
                        foreach ($sim['data'] as &$point) {
                            $point['algo'] = $algoName;
                        }
                        $finalData = array_merge($finalData, $sim['data']);
                    }
                }
            }
            else {
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

    /**
     * Gère l'importation d'un fichier CSV personnalisé par un utilisateur.
     * @return void Redirige vers le dashboard avec un message flash.
     */
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

    /**
     * Supprime les données CSV personnalisées de l'utilisateur connecté.
     * @return void Redirige vers le dashboard avec un message flash.
     */
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
     * Enregistre le choix de l'algorithme de prédiction.
     * @return void Redirige vers le dashboard avec un message flash.
     * @throws \Exception En cas d'erreur d'écriture ou d'algorithme non reconnu.
     */
    public function setAlgorithm(): void {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCsrf();
                
                $algo = $_POST['algo'] ?? 'standard';
                
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
        
        $this->redirect('/dashboard');
    }
}