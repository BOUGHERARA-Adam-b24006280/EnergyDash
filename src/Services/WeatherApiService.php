<?php
/**
 * Fichier : WeatherApiService.php
 * Rôle : Fichier contenant le client HTTP pour l'API Open-Meteo.
 */

namespace App\Services;

/**
 * Service faisant office de client pour l'API externe Open-Meteo.
 */
class WeatherApiService 
{

    /** @var array $coordinates Tableau associatif statique stockant la latitude et la longitude des villes prédéfinies. */
    private array $coordinates = [
        'lyon'      => ['lat' => 45.76, 'lon' => 4.83],
        'paris'     => ['lat' => 48.85, 'lon' => 2.35],
        'marseille' => ['lat' => 43.29, 'lon' => 5.36],
        'nice'      => ['lat' => 43.71, 'lon' => 7.26],
        'grenoble'  => ['lat' => 45.18, 'lon' => 5.72],
        'bordeaux'  => ['lat' => 44.83, 'lon' => -0.57]
    ];

    /**
     * Récupère les données météorologiques horaires depuis Open-Meteo pour une période donnée.
     * @param string $city Le nom de la ville pour laquelle on souhaite la météo.
     * @param string $startDate La date de début de l'extraction au format 'Y-m-d'.
     * @param string $endDate La date de fin de l'extraction au format 'Y-m-d'.
     * @return array Un tableau contenant la liste formatée des relevés météo horaires. Retourne un tableau vide en cas d'erreur de l'API.
     */
    public function getHourlyWeather(string $city, string $startDate, string $endDate): array 
    {
        $cityKey = strtolower($city);
        $coords = $this->coordinates[$cityKey] ?? $this->coordinates['lyon'];
        $today = date('Y-m-d');

        if ($endDate < $today) {
            $apiUrl = "https://archive-api.open-meteo.com/v1/archive?latitude={$coords['lat']}&longitude={$coords['lon']}&start_date={$startDate}&end_date={$endDate}&hourly=temperature_2m,precipitation,wind_speed_10m,shortwave_radiation&timezone=Europe%2FParis";
        } else {
            $apiUrl = "https://api.open-meteo.com/v1/forecast?latitude={$coords['lat']}&longitude={$coords['lon']}&start_date={$startDate}&end_date={$endDate}&hourly=temperature_2m,precipitation,wind_speed_10m,shortwave_radiation&timezone=Europe%2FParis";
        }
        
        $context = stream_context_create([
            "ssl" => ["verify_peer" => false, "verify_peer_name" => false],
            "http" => ["timeout" => 5]
        ]);

        $json = @file_get_contents($apiUrl, false, $context);
        if ($json === false) return [];

        $apiData = json_decode($json, true);

        if (!is_array($apiData) || !isset($apiData['hourly']) || !is_array($apiData['hourly'])) {
            return [];
        }

        return $this->formatWeatherData($apiData['hourly'], $startDate, $endDate);
    }

    /**
     * Nettoie et formate les données brutes reçues de l'API Open-Meteo en une structure plus lisible et normalisée.
     * @param array $hourly Le tableau multidimensionnel brut retourné par la clé 'hourly' de l'API Open-Meteo.
     * @param string $startDate La date de début au format 'Y-m-d' utilisée pour filtrer et conserver uniquement les jours demandés.
     * @param string $endDate La date de fin au format 'Y-m-d' utilisée pour filtrer et conserver uniquement les jours demandés.
     * @return array Un tableau indexé de relevés météo horaires contenant les clés 'date', 'temp', 'rain', 'wind', et 'sun'.
     */
    private function formatWeatherData(array $hourly, string $startDate, string $endDate): array
    {
        $weatherList = [];

        if (isset($hourly['time']) && is_array($hourly['time'])) {
            foreach ($hourly['time'] as $index => $isoDate) {
                if (!is_string($isoDate)) continue;

                $dateString = str_replace('T', ' ', $isoDate) . ':00';
                $dayOnly = substr($dateString, 0, 10);
                
                if ($dayOnly < $startDate || $dayOnly > $endDate) continue;

                $weatherList[] = [
                    'date' => $dateString,
                    'temp' => (float)($hourly['temperature_2m'][$index] ?? 0.0),
                    'rain' => (float)($hourly['precipitation'][$index] ?? 0.0),
                    'wind' => (float)($hourly['wind_speed_10m'][$index] ?? 0.0),
                    'sun'  => (float)($hourly['shortwave_radiation'][$index] ?? 0.0)
                ];
            }
        }

        return $weatherList;
    }
}