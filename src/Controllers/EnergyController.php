<?php
namespace App\Controllers;

use App\Services\EnergyCsvService;
use App\Services\FileUploadService;
use App\Services\PredictionService;

class EnergyController extends \App\Core\Controller {
    
    public function index(): void {
        $type = $this->sanitize($_GET['type'] ?? 'all');
        $city = $this->sanitize($_GET['city'] ?? 'all'); 
        $compare = (!empty($_GET['compare'])) ? $this->sanitize($_GET['compare']) : null;
        $from = $this->sanitize($_GET['from'] ?? date('Y-m-01'));
        $to = (!empty($_GET['to'])) ? $this->sanitize($_GET['to']) : $from;

        try {
            // 1. Initialiser les services avec l'ID de l'utilisateur
            $userId = $_SESSION['user']['id'] ?? null;
            $csvService = new EnergyCsvService($userId ? (int)$userId : null);
            $predictionService = new PredictionService($csvService);

            // 2. Récupérer les données passées
            $realData = $csvService->getEnergyData($type, $city, $from, $to, $compare);
            $finalData = $realData['data'] ?? []; 
            
            // 3. Mode Backtest (Admin)
            $isBacktest = ($_GET['backtest'] ?? 'false') === 'true';
            $isAdmin = (is_array($_SESSION['user'] ?? null) && in_array($_SESSION['user']['role'] ?? '', ['admin', 'editor']));

            if ($isAdmin && $isBacktest) {
                $targetType = ($type === 'all') ? 'solaire' : $type;
                
                // Appel au nouveau PredictionService !
                $simStandard = $predictionService->simulateStandard($targetType, $city, $from, $to);
                foreach($simStandard['data'] as &$d) { $d['algo'] = 'standard'; }
                
                // NOTE: On intégrera l'appel à Prev_Deep_Learning ici plus tard !
                
                $finalData = array_merge($finalData, $simStandard['data']);
            } 
            else {
                // Mode normal (Futur)
                $lastDateFound = empty($finalData) ? date('Y-m-d', strtotime($from . ' -1 day')) : substr(end($finalData)['date'], 0, 10);

                if ($lastDateFound < $to) {
                    $simStart = date('Y-m-d', strtotime($lastDateFound . ' +1 day'));
                    $simEnd = ($to > $simStart) ? $simStart : $to;

                    $targetType = ($type === 'all') ? 'solaire' : $type;
                    
                    // Appel au nouveau PredictionService !
                    $simulated = $predictionService->simulateStandard($targetType, $city, $simStart, $simEnd);
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

    public function setAlgorithm(): void {
        // Logique simplifiée en attendant l'intégration finale de l'IA
        $this->redirect('/dashboard');
    }
}