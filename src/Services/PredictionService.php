<?php
/**
 * Fichier : PredictionService.php
 * Rôle : Fichier contenant le service de prédiction standard.
 */

namespace App\Services;

/**
 * Service encapsulant l'algorithme de prédiction standard par calculs mathématiques.
 */
class PredictionService 
{
    /** @var WeatherApiService $weatherApi L'instance du service permettant de requêter l'API météo externe. */
    private WeatherApiService $weatherApi;

    /** @var EnergyCsvService $csvService L'instance de service permettant d'accéder aux données historiques du fichier CSV. */
    private EnergyCsvService $csvService;

    /** 
     * Constructeur injectant les services dépendants nécessaires aux calculs mathématiques de prédiction.
     * @param EnergyCsvService $csvService L'instance du service CSV déjà initialisée.
     */
    public function __construct(EnergyCsvService $csvService)
    {
        $this->weatherApi = new WeatherApiService();
        $this->csvService = $csvService;
    }

    /**
     * Simule la production énergétique d'une ville selon un algorithme empirique standard basé sur
     * les données météorologiques et le ratio de performance historique (ou un ratio par défaut si introuvable).
     * @param string $type Le type d'énergie à simuler.
     * @param string $city La ville sur laquelle s'applique la simulation.
     * @param string $startDate La date de début de la simulation au format 'Y-m-d'.
     * @param string $endDate La date de fin de la simulation au format 'Y-m-d'.
     * @return array Un tableau contenant les métadonnées de requête et la série temporelle ('data') des productions horaires estimées.
     */
    public function simulateStandard(string $type, string $city, string $startDate, string $endDate): array 
    {
        $weatherData = $this->weatherApi->getHourlyWeather($city, $startDate, $endDate);
        $ratio = $this->csvService->getPerformanceRatio($type, $city);
        
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