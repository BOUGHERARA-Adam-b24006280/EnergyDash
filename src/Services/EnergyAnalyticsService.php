<?php
/**
 * Fichier : EnergyAnalyticsService.php
 * Rôle : Service gérant la logique métier et les calculs statistiques sur l'énergie.
 */

namespace App\Services;

use App\Repositories\EnergyRepository;

/**
 * Ce service est responsable des analyses de données et des calculs statistiques.
 */
class EnergyAnalyticsService {

    /** @var EnergyRepository Instance du dépôt de données énergétiques. */
    private EnergyRepository $repository;

    /**
     * Constructeur de la classe.
     * * @param EnergyRepository $repository Le dépôt utilisé pour récupérer les données historiques.
     */
    public function __construct(EnergyRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * Calcule le ratio de performance historique (Production / Valeur Météo).
     * @param string $type Le type d'énergie.
     * @param string $city Le nom de la ville concernée.
     * @return float Le ratio de performance moyen calculé. Retourne 0 si aucune donnée n'est disponible.
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