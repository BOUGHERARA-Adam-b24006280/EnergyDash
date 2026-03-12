<?php
/**
 * Fichier : WeatherApiService.php
 * Rôle : Fichier contenant le client HTTP pour l'API Open-Meteo avec système de cache et mode secours.
 */

namespace App\Services;

/**
 * Service faisant office de client pour l'API externe Open-Meteo.
 * Intègre un système de cache local pour éviter les limitations de requêtes (HTTP 429)
 * et un mode de génération de données de secours en cas d'échec total de l'API.
 */
class WeatherApiService 
{

    /** @var array<string, array{lat: float, lon: float}> $coordinates Tableau associatif statique stockant la latitude et la longitude des villes prédéfinies. */
    private array $coordinates = [
        'lyon'      => ['lat' => 45.76, 'lon' => 4.83],
        'paris'     => ['lat' => 48.85, 'lon' => 2.35],
        'marseille' => ['lat' => 43.29, 'lon' => 5.36],
        'nice'      => ['lat' => 43.71, 'lon' => 7.26],
        'grenoble'  => ['lat' => 45.18, 'lon' => 5.72],
        'bordeaux'  => ['lat' => 44.83, 'lon' => -0.57]
    ];

    /** @var string $cachePath Chemin vers le dossier de stockage du cache météo. */
    private string $cachePath = __DIR__ . '/../../Storage/cache/weather/';

    /**
     * Constructeur : Initialise le dossier de cache s'il n'existe pas.
     */
    public function __construct() 
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }

    /**
     * Récupère les données météorologiques horaires depuis Open-Meteo pour une période donnée.
     * Utilise le cache local si disponible pour éviter les erreurs "429 Too Many Requests".
     * En cas d'échec de l'API (quota IP dépassé sur AlwaysData), génère des données de secours cohérentes.
     * * @param string $city Le nom de la ville pour laquelle on souhaite la météo.
     * @param string $startDate La date de début de l'extraction au format 'Y-m-d'.
     * @param string $endDate La date de fin de l'extraction au format 'Y-m-d'.
     * @return array<int, array{date: string, temp: float, rain: float, wind: float, sun: float}> Un tableau contenant la liste formatée des relevés météo horaires.
     */
    public function getHourlyWeather(string $city, string $startDate, string $endDate): array 
    {
        $cityKey = strtolower($city);
        $coords = $this->coordinates[$cityKey] ?? $this->coordinates['lyon'];
        $today = date('Y-m-d');

        $cacheFile = $this->cachePath . "{$cityKey}_{$startDate}_{$endDate}.json";

        if (file_exists($cacheFile)) {
            $mtime = filemtime($cacheFile);
            if ($mtime !== false && (time() - $mtime < 86400)) {
                $cachedContent = file_get_contents($cacheFile);
                if ($cachedContent !== false) {
                    /** @var array<int, array{date: string, temp: float, rain: float, wind: float, sun: float}>|null $cachedData */
                    $cachedData = json_decode($cachedContent, true);
                    if (is_array($cachedData)) {
                        return $cachedData;
                    }
                }
            }
        }

        if ($endDate < $today) {
            $apiUrl = "https://archive-api.open-meteo.com/v1/archive?latitude={$coords['lat']}&longitude={$coords['lon']}&start_date={$startDate}&end_date={$endDate}&hourly=temperature_2m,precipitation,wind_speed_10m,shortwave_radiation&timezone=Europe%2FParis";
        } else {
            $apiUrl = "https://api.open-meteo.com/v1/forecast?latitude={$coords['lat']}&longitude={$coords['lon']}&start_date={$startDate}&end_date={$endDate}&hourly=temperature_2m,precipitation,wind_speed_10m,shortwave_radiation&timezone=Europe%2FParis";
        }
        
        $context = stream_context_create([
            "ssl" => ["verify_peer" => false, "verify_peer_name" => false],
            "http" => [
                "timeout" => 5,
                "ignore_errors" => true,
                "header" => "User-Agent: EnergyDash-App/1.0\r\n"
            ]
        ]);

        $json = @file_get_contents($apiUrl, false, $context);

        if ($json === false || strpos($http_response_header[0], '200') === false) {
            if (file_exists($cacheFile)) {
                $staleContent = file_get_contents($cacheFile);
                if ($staleContent !== false) {
                    /** @var array<int, array{date: string, temp: float, rain: float, wind: float, sun: float}>|null $staleData */
                    $staleData = json_decode($staleContent, true);
                    if (is_array($staleData)) {
                        return $staleData;
                    }
                }
            }
            return $this->generateRescueData($startDate, $endDate);
        }

        /** @var array<string, mixed>|null $apiData */
        $apiData = json_decode((string)$json, true);

        if (!is_array($apiData) || !isset($apiData['hourly']) || !is_array($apiData['hourly'])) {
            return $this->generateRescueData($startDate, $endDate);
        }

        /** @var array<string, array<int, mixed>> $hourlyData */
        $hourlyData = $apiData['hourly'];

        $formattedData = $this->formatWeatherData($hourlyData, $startDate, $endDate);

        if (!empty($formattedData)) {
            file_put_contents($cacheFile, json_encode($formattedData));
        }

        return $formattedData;
    }

    /**
     * Génère des données météorologiques de secours cohérentes en cas d'indisponibilité de l'API.
     * Cela permet au système de prédiction de continuer à fonctionner même en cas de quota dépassé.
     * * @param string $start Date de début (Y-m-d).
     * @param string $end Date de fin (Y-m-d).
     * @return array<int, array{date: string, temp: float, rain: float, wind: float, sun: float}>
     */
    private function generateRescueData(string $start, string $end): array 
    {
        $list = [];
        $startTime = strtotime($start);
        $endTime = strtotime($end);

        if ($startTime === false || $endTime === false) {
            return [];
        }

        $current = $startTime;
        while ($current <= $endTime) {
            $day = date('Y-m-d', $current);
            for ($h = 0; $h < 24; $h++) {
                $hourStr = str_pad((string)$h, 2, '0', STR_PAD_LEFT);
                $list[] = [
                    'date' => "{$day} {$hourStr}:00:00",
                    'temp' => (float)rand(12, 22),
                    'rain' => (float)(rand(0, 10) > 8 ? rand(1, 4) : 0),
                    'wind' => (float)rand(5, 25),
                    'sun'  => (float)($h > 7 && $h < 19 ? rand(150, 600) : 0)
                ];
            }
            $current = strtotime("+1 day", $current) ?: ($endTime + 1);
        }
        return $list;
    }

    /**
     * Nettoie et formate les données brutes reçues de l'API Open-Meteo en une structure plus lisible et normalisée.
     * @param array<string, array<int, mixed>> $hourly Le tableau multidimensionnel brut retourné par la clé 'hourly' de l'API Open-Meteo.
     * @param string $startDate La date de début au format 'Y-m-d' utilisée pour filtrer et conserver uniquement les jours demandés.
     * @param string $endDate La date de fin au format 'Y-m-d' utilisée pour filtrer et conserver uniquement les jours demandés.
     * @return array<int, array{date: string, temp: float, rain: float, wind: float, sun: float}> Un tableau indexé de relevés météo horaires contenant les clés 'date', 'temp', 'rain', 'wind', et 'sun'.
     */
    private function formatWeatherData(array $hourly, string $startDate, string $endDate): array
    {
        $weatherList = [];
        $timeData = $hourly['time'] ?? null;

        if ($timeData !== null) {
            foreach ($timeData as $index => $isoDate) {
                if (!is_string($isoDate)) continue;

                $dateString = str_replace('T', ' ', $isoDate) . ':00';
                $dayOnly = substr($dateString, 0, 10);
                
                if ($dayOnly < $startDate || $dayOnly > $endDate) continue;

                $temp = $hourly['temperature_2m'][$index] ?? 0.0;
                $rain = $hourly['precipitation'][$index] ?? 0.0;
                $wind = $hourly['wind_speed_10m'][$index] ?? 0.0;
                $sun  = $hourly['shortwave_radiation'][$index] ?? 0.0;

                $weatherList[] = [
                    'date' => $dateString,
                    'temp' => is_numeric($temp) ? (float)$temp : 0.0,
                    'rain' => is_numeric($rain) ? (float)$rain : 0.0,
                    'wind' => is_numeric($wind) ? (float)$wind : 0.0,
                    'sun'  => is_numeric($sun) ? (float)$sun : 0.0
                ];
            }
        }

        return $weatherList;
    }
}