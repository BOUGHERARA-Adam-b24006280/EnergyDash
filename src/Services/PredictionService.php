<?php
/**
 * Fichier : PredictionService.php
 * Rôle : Implémente l'algorithme de prédiction standard basé sur les ratios de performance.
 */

namespace App\Services;

use App\Contracts\PredictionStrategyInterface;

/**
 * Ce service implémente la stratégie de prédiction "Standard".
 */
class PredictionService implements PredictionStrategyInterface 
{
    /** @var WeatherApiService Instance du service de récupération des données météo. */
    private WeatherApiService $weatherApi;

    /** @var EnergyAnalyticsService Instance du service d'analyse des performances historiques. */
    private EnergyAnalyticsService $analyticsService;

    /**
     * Initialise le service météo et injecte le service d'analyse.
     * @param EnergyAnalyticsService $analyticsService Le service utilisé pour obtenir les ratios d'efficacité.
     */
    public function __construct(EnergyAnalyticsService $analyticsService)
    {
        $this->weatherApi = new WeatherApiService();
        $this->analyticsService = $analyticsService;
    }

    /**
     * Exécute la simulation de production basée sur un modèle mathématique linéaire.
     * @param string $type Le type d'énergie à prédire.
     * @param string $city La ville cible pour la simulation.
     * @param string $startDate Date de début de la simulation (Y-m-d).
     * @param string $endDate Date de fin de la simulation (Y-m-d).
     * @return array<string, mixed> Données de prédiction formatées pour l'affichage graphique.
     */
    public function predict(string $type, string $city, string $startDate, string $endDate): array 
    {
        $weatherData = $this->weatherApi->getHourlyWeather($city, $startDate, $endDate);
        $ratio = $this->analyticsService->getPerformanceRatio($type, $city);
        
        if ($ratio <= 0) {
            if ($type === 'solaire') $ratio = 0.5;
            elseif ($type === 'eolien') $ratio = 15;
            else $ratio = 5;
        }

        $predictions = [];
        foreach ($weatherData as $w) {
            $predictedProd = 0;
            $meteoValueForChart = 0;

            if ($type === 'solaire') {
                $predictedProd = $w['sun'] * $ratio;
                if ($w['temp'] > 25) $predictedProd *= 0.95; 
                $meteoValueForChart = $w['sun'];
            } elseif ($type === 'eolien') {
                if ($w['wind'] > 10) $predictedProd = $w['wind'] * $ratio;
                $meteoValueForChart = $w['wind'];
            } elseif ($type === 'hydraulique') {
                $predictedProd = 5.0 + ($w['rain'] * $ratio * 10);
                $meteoValueForChart = $w['rain'];
            }

            $predictions[] = [
                'date' => $w['date'],
                'production' => round($predictedProd, 2),
                'ville' => $city,
                'meteo' => $meteoValueForChart,
                'temp' => $w['temp'],
                'statut' => 'prevision',
                'algo' => 'standard'
            ];
        }

        return ['type' => $type, 'city' => $city, 'from' => $startDate, 'to' => $endDate, 'data' => $predictions];
    }
}