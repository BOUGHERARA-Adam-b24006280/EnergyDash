<?php
namespace App\Services;

use App\Repositories\EnergyRepository;

/**
 * Service gérant la logique métier et les calculs statistiques sur l'énergie.
 */
class EnergyAnalyticsService {
    private EnergyRepository $repository;

    public function __construct(EnergyRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * Calcule le ratio de performance historique (Production / Valeur Météo).
     */
    public function getPerformanceRatio(string $type, string $city): float {
        $historicalData = $this->repository->getHistoricalDataForRatio($type, $city);
        
        $totalRatio = 0; 
        $count = count($historicalData);

        if ($count === 0) return 0;

        foreach ($historicalData as $data) {
            $totalRatio += ($data['production'] / $data['meteo']);
        }

        return $totalRatio / $count;
    }
}