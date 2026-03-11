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
        $typeParam = $_GET['type'] ?? 'all';
        $type = $this->sanitize(is_string($typeParam) ? $typeParam : 'all');

        $cityParam = $_GET['city'] ?? 'all';
        $city = $this->sanitize(is_string($cityParam) ? $cityParam : 'all'); 

        $compareParam = $_GET['compare'] ?? null;
        $compare = (is_string($compareParam) && $compareParam !== '') ? $this->sanitize($compareParam) : null;

        $fromParam = $_GET['from'] ?? date('Y-m-01');
        $from = $this->sanitize(is_string($fromParam) ? $fromParam : date('Y-m-01'));

        $toParam = $_GET['to'] ?? $from;
        $to = (is_string($toParam) && $toParam !== '') ? $this->sanitize($toParam) : $from;

        try {
            $userSession = $_SESSION['user'] ?? null;
            $userId = null;
            $userRole = '';

            if (is_array($userSession)) {
                $userId = $userSession['id'] ?? null;
                $userRole = is_string($userSession['role'] ?? null) ? $userSession['role'] : '';
            }

            $idSuffix = is_numeric($userId) ? (string)$userId : 'default';
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
            $isAdmin = ($userRole === 'admin');

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
                        
                        $simData = $sim['data'] ?? null;
                        if (is_array($simData)) {
                            foreach ($simData as &$point) {
                                if (is_array($point)) {
                                    $point['algo'] = $algoName;
                                }
                            }
                            /** @var array<int, array<string, mixed>> $simData */
                            $finalData = array_merge($finalData, $simData);
                        }
                    }
                }
            }
            else {
                if (empty($finalData)) {
                    $ts = strtotime($from . ' -1 day');
                    $lastDateFound = ($ts !== false) ? date('Y-m-d', $ts) : $from;
                } else {
                    $lastItem = end($finalData);
                    $lastDateFound = substr($lastItem['date'], 0, 10);
                }

                if ($lastDateFound < $to && $city !== 'all') {
                    $tsNext = strtotime($lastDateFound . ' +1 day');
                    $simStart = ($tsNext !== false) ? date('Y-m-d', $tsNext) : $lastDateFound;
                    
                    $targetType = ($type === 'all') ? 'solaire' : $type;
                    $simulated = $predictionAlgorithm->predict($targetType, $city, $simStart, $to);
                    
                    $simData = $simulated['data'] ?? null;
                    if (is_array($simData)) {
                        /** @var array<int, array<string, mixed>> $simData */
                        $finalData = array_merge($finalData, $simData);
                    }
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
        $csvFile = $_FILES['csv_file'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_array($csvFile)) {
            try {
                $this->validateCsrf();
                $userSession = $_SESSION['user'] ?? null;
                $userIdVal = (is_array($userSession) && isset($userSession['id'])) ? $userSession['id'] : null;

                if (!is_numeric($userIdVal)) {
                    throw new \Exception("Utilisateur non identifié.");
                }

                $userId = (int)$userIdVal;
                $uploadService = new FileUploadService();
                
                /** @var array<string, mixed> $csvFile */
                $uploadService->handleCsvUpload($csvFile, $userId);
                
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
                $userSession = $_SESSION['user'] ?? null;
                $userIdVal = (is_array($userSession) && isset($userSession['id'])) ? $userSession['id'] : null;

                if (!is_numeric($userIdVal)) {
                    throw new \Exception("Action impossible.");
                }

                $userId = (int)$userIdVal;
                
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