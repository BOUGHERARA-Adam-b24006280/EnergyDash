<?php
namespace App\Services;

class PredictionService 
{
    private WeatherApiService $weatherApi;
    
    // 1. On remplace l'ancienne propriété par le nouveau service d'analyse
    private EnergyAnalyticsService $analyticsService;

    // 2. On modifie le constructeur pour accepter le nouveau service
    public function __construct(EnergyAnalyticsService $analyticsService)
    {
        $this->weatherApi = new WeatherApiService();
        $this->analyticsService = $analyticsService;
    }

    /**
     * Votre algorithme d'origine (sauvegardé et isolé ici !)
     */
    public function simulateStandard(string $type, string $city, string $startDate, string $endDate): array 
    {
        $weatherData = $this->weatherApi->getHourlyWeather($city, $startDate, $endDate);
        
        // 3. On appelle le nouveau service pour calculer le ratio
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
                if ($w['temp'] > 25) $predictedProd *= 0.95; // Malus chaleur
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