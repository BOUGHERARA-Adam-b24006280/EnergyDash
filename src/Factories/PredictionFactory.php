<?php
namespace App\Factories;

use App\Contracts\PredictionStrategyInterface;
use App\Services\PredictionService;
use App\Strategies\DeepLearningStrategy;
use App\Services\EnergyAnalyticsService;
use App\Repositories\EnergyRepository;

class PredictionFactory 
{
    /**
     * Lit le fichier de configuration et renvoie le bon algorithme de prédiction.
     */
    public static function make(EnergyRepository $repository, EnergyAnalyticsService $analyticsService): PredictionStrategyInterface 
    {
        $algoFile = __DIR__ . '/../../Storage/active_algo.txt';
        $activeAlgo = file_exists($algoFile) ? trim(file_get_contents($algoFile)) : 'standard';

        if ($activeAlgo === 'deep_learning') {
            return new DeepLearningStrategy($repository);
        }

        // Par défaut
        return new PredictionService($analyticsService);
    }
}