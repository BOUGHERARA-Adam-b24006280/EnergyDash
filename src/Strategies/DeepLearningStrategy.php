<?php
namespace App\Strategies;

use App\Contracts\PredictionStrategyInterface;
use App\Services\DeepLearningPredictionService;
use App\Repositories\EnergyRepository;
use App\Services\WeatherApiService;

class DeepLearningStrategy implements PredictionStrategyInterface 
{
    private EnergyRepository $repository;
    private WeatherApiService $weatherApi;

    public function __construct(EnergyRepository $repository) {
        $this->repository = $repository;
        $this->weatherApi = new WeatherApiService();
    }

    public function predict(string $type, string $city, string $startDate, string $endDate): array 
    {
        // 1. Récupération des données historiques pour l'entraînement de l'IA
        $historicalData = $this->repository->getHistoricalDataForRatio($type, $city);
        
        $meteoTypes = []; $temps = []; $meteoDatas = []; $archiveDatas = [];

        // Sécurité : Si pas d'historique, on met des données factices pour éviter le crash de l'IA
        if (empty($historicalData)) {
            $meteoTypes = [$type]; $temps = [20]; $meteoDatas = [50]; $archiveDatas = [100];
        } else {
            foreach ($historicalData as $data) {
                $meteoTypes[] = $type;
                $temps[] = 20; // Température moyenne en dur (à améliorer plus tard)
                $meteoDatas[] = $data['meteo'];
                $archiveDatas[] = $data['production'];
            }
        }

        // 2. Entraînement du modèle (God object : Rubix ML)
        $iaService = new DeepLearningPredictionService($meteoTypes, $temps, $meteoDatas, $archiveDatas);

        // 3. Récupération de la météo future
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

        // 4. Prédiction finale
        $predictions = $iaService->predict($predMeteoTypes, $predTemps, $predMeteoDatas, $predDates, $city);

        return ['type' => $type, 'city' => $city, 'from' => $startDate, 'to' => $endDate, 'data' => $predictions];
    }
}