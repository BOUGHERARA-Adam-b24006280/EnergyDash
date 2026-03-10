<?php
namespace App\Factories;

use App\Contracts\PredictionStrategyInterface;
use App\Services\PredictionService;
use App\Strategies\DeepLearningStrategy;
use App\Services\EnergyAnalyticsService;
use App\Repositories\EnergyRepository;

class PredictionFactory 
{
    public static function make(EnergyRepository $repository, EnergyAnalyticsService $analyticsService): PredictionStrategyInterface 
    {
        $algoFile = __DIR__ . '/../../Storage/active_algo.txt';
        $activeAlgo = file_exists($algoFile) ? trim(file_get_contents($algoFile)) : 'standard';

        // On vérifie le mot 'lstm' ici !
        if ($activeAlgo === 'lstm') {
            return new DeepLearningStrategy($repository);
        }

        return new PredictionService($analyticsService);
    }
}