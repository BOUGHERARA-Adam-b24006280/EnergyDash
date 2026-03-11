<?php
/**
 * Fichier : PredictionFactory.php
 * Rôle : Responsable de l'instanciation dynamique des stratégies de prédiction.
 */

namespace App\Factories;

use App\Contracts\PredictionStrategyInterface;
use App\Services\PredictionService;
use App\Strategies\DeepLearningStrategy;
use App\Services\EnergyAnalyticsService;
use App\Repositories\EnergyRepository;

/**
 * Cette classe implémente la logique de sélection de l'algorithme.
 */
class PredictionFactory 
{

    /**
     * Crée et retourne l'instance de la stratégie de prédiction active.
     * @param EnergyRepository $repository Le dépôt de données pour l'accès aux historiques.
     * @param EnergyAnalyticsService $analyticsService Le service d'analyse pour le calcul des ratios.
     * @return PredictionStrategyInterface Une instance de l'algorithme choisi respectant l'interface commune.
     */
    public static function make(EnergyRepository $repository, EnergyAnalyticsService $analyticsService): PredictionStrategyInterface 
    {
        $algoFile = __DIR__ . '/../../Storage/active_algo.txt';
        $activeAlgo = 'standard';

        if (file_exists($algoFile)) {
            $content = file_get_contents($algoFile);
            if (is_string($content)) {
                $activeAlgo = trim($content);
            }
        }

        if ($activeAlgo === 'lstm') {
            return new DeepLearningStrategy($repository);
        }

        return new PredictionService($analyticsService);
    }
}