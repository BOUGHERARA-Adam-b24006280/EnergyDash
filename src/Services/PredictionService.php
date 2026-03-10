<?php
namespace App\Services;

use App\Contracts\PredictionStrategyInterface;

class PredictionService implements PredictionStrategyInterface 
{
    private WeatherApiService $weatherApi;
    private EnergyAnalyticsService $analyticsService;

    public function __construct(EnergyAnalyticsService $analyticsService)
    {
        $this->weatherApi = new WeatherApiService();
        $this->analyticsService = $analyticsService;
    }

    /**
     * L'ancienne méthode "simulateStandard" s'appelle maintenant "predict" pour respecter l'interface.
     */
    public function predict(string $type, string $city, string $startDate, string $endDate): array 
    {
        $weatherData = $this->weatherApi->getHourlyWeather($city, $startDate, $endDate);
        $ratio = $this->analyticsService->getPerformanceRatio($type, $city);
        
        if ($ratio <= 0) {
            if ($type === 'solaire') $ratio = 0.005; 
            elseif ($type === 'eolien') $ratio = 0.1; 
            else $ratio = 0.5;
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
                'statut' => 'prevision'
            ];
        }

        return ['type' => $type, 'city' => $city, 'from' => $startDate, 'to' => $endDate, 'data' => $predictions];
    }
}