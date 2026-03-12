<?php
/**
 * Fichier : DeepLearningStrategy.php
 * Rôle : Implémente l'algorithme de prédiction basé sur l'apprentissage profond (Deep Learning).
 */

namespace App\Strategies;

use App\Contracts\PredictionStrategyInterface;
use App\Services\DeepLearningPredictionService;
use App\Repositories\EnergyRepository;
use App\Services\WeatherApiService;

/**
 * Ce service implémente la stratégie de prédiction par "Deep Learning".
 */
class DeepLearningStrategy implements PredictionStrategyInterface 
{
    /** @var EnergyRepository Instance du dépôt pour l'accès aux données historiques d'entraînement. */
    private EnergyRepository $repository;

    /** @var WeatherApiService Instance du service client pour l'API météo. */
    private WeatherApiService $weatherApi;

    /**
     * Initialise le dépôt de données et le service météo.
     * @param EnergyRepository $repository Le dépôt utilisé pour extraire l'historique d'entraînement.
     */
    public function __construct(EnergyRepository $repository) {
        $this->repository = $repository;
        $this->weatherApi = new WeatherApiService();
    }

    /**
     * Exécute la simulation de production via un modèle d'IA entraîné.
     * @param string $type Le type d'énergie à prédire.
     * @param string $city La ville cible pour la prédiction.
     * @param string $startDate Date de début de la période de prédiction (Y-m-d).
     * @param string $endDate Date de fin de la période de prédiction (Y-m-d).
     * @return array<string, mixed> Données de prédiction formatées pour l'affichage.
     */
    public function predict(string $type, string $city, string $startDate, string $endDate): array 
    {
        $historicalData = $this->repository->getHistoricalDataForRatio($type, $city);
        
        $meteoTypes = []; $temps = []; $meteoDatas = []; $archiveDatas = [];

        if (empty($historicalData)) {
            $meteoTypes = [$type]; $temps = [20]; $meteoDatas = [50]; $archiveDatas = [100];
        } else {
            foreach ($historicalData as $data) {
                $meteoTypes[] = $type;
                $temps[] = 20;
                $meteoDatas[] = $data['meteo'];
                $archiveDatas[] = $data['production'];
            }
        }

        $iaService = new DeepLearningPredictionService($meteoTypes, $temps, $meteoDatas, $archiveDatas);

        $futureWeather = $this->weatherApi->getHourlyWeather($city, $startDate, $endDate);
        
        $predMeteoTypes = []; $predTemps = []; $predMeteoDatas = []; $predDates = [];
        foreach ($futureWeather as $w) {
            $predMeteoTypes[] = $type;
            $predTemps[] = $w['temp'];
            $predMeteoDatas[] = ($type === 'solaire') ? $w['sun'] : (($type === 'eolien') ? $w['wind'] : $w['rain']);
            $predDates[] = $w['date'];
        }

        if (empty($predDates)) {
            return ['type' => $type, 'city' => $city, 'from' => $startDate, 'to' => $endDate, 'data' => []];
        }

        $predictions = $iaService->predict($predMeteoTypes, $predTemps, $predMeteoDatas, $predDates, $city);

        return ['type' => $type, 'city' => $city, 'from' => $startDate, 'to' => $endDate, 'data' => $predictions];
    }
}