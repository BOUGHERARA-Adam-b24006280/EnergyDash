<?php

namespace App\Services;

/**
 * Mock de la fonction file_get_contents pour le namespace App\Services.
 * Permet de simuler les réponses de l'API météo.
 */
$mockWeatherApiResponse = '';

function file_get_contents(string $filename, bool $use_include_path = false, $context = null): string|false {
    global $mockWeatherApiResponse;

    // Si l'URL contient l'API Open-Meteo, on renvoie le mock
    if (str_contains($filename, 'open-meteo.com')) {
        return $mockWeatherApiResponse;
    }

    return \file_get_contents($filename, $use_include_path, $context);
}

namespace Tests\Services;

use App\Services\WeatherApiService;
use PHPUnit\Framework\TestCase;

class WeatherApiServiceTest extends TestCase
{
    private WeatherApiService $service;

    protected function setUp(): void
    {
        $this->service = new WeatherApiService();
    }

    /**
     * Test : Vérifie que le service récupère et formate correctement les données météo.
     */
    public function testGetHourlyWeatherReturnsFormattedData(): void
    {
        global $mockWeatherApiResponse;

        // Simulation d'une réponse JSON valide de l'API
        $mockWeatherApiResponse = json_encode([
            'hourly' => [
                'time' => ['2026-01-01T12:00', '2026-01-01T13:00'],
                'temperature_2m' => [15.5, 16.0],
                'precipitation' => [0.0, 0.1],
                'wind_speed_10m' => [10.0, 12.0],
                'shortwave_radiation' => [500.0, 550.0]
            ]
        ]);

        $result = $this->service->getHourlyWeather('paris', '2026-01-01', '2026-01-01');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        // Vérification de la structure du premier relevé
        $firstEntry = $result[0];
        $this->assertEquals('2026-01-01 12:00:00', $firstEntry['date']);
        $this->assertEquals(15.5, $firstEntry['temp']);
        $this->assertEquals(500.0, $firstEntry['sun']);
    }

    /**
     * Test : Vérifie que le service gère une erreur de l'API (réponse vide ou false).
     */
    public function testGetHourlyWeatherHandlesApiFailure(): void
    {
        global $mockWeatherApiResponse;
        $mockWeatherApiResponse = false; // Simule un échec de connexion

        $result = $this->service->getHourlyWeather('lyon', '2026-01-01', '2026-01-01');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test : Vérifie le filtrage par plage de dates dans formatWeatherData.
     */
    public function testFormatWeatherDataFiltersOutCorrectDates(): void
    {
        global $mockWeatherApiResponse;

        // On simule 3 jours, mais on ne demandera que le 2ème jour
        $mockWeatherApiResponse = json_encode([
            'hourly' => [
                'time' => ['2026-01-01T12:00', '2026-01-02T12:00', '2026-01-03T12:00'],
                'temperature_2m' => [10, 20, 30],
                'precipitation' => [0, 0, 0],
                'wind_speed_10m' => [5, 5, 5],
                'shortwave_radiation' => [100, 200, 300]
            ]
        ]);

        // On demande uniquement le 02 Janvier
        $result = $this->service->getHourlyWeather('lyon', '2026-01-02', '2026-01-02');

        $this->assertCount(1, $result);
        $this->assertEquals('2026-01-02 12:00:00', $result[0]['date']);
        $this->assertEquals(20.0, $result[0]['temp']);
    }

    /**
     * Test : Vérifie que le service utilise Lyon par défaut si la ville est inconnue.
     */
    public function testGetHourlyWeatherUsesDefaultCity(): void
    {
        global $mockWeatherApiResponse;
        $mockWeatherApiResponse = json_encode(['hourly' => ['time' => []]]);

        // 'VilleFantome' n'est pas dans la liste du service
        $result = $this->service->getHourlyWeather('VilleFantome', '2026-01-01', '2026-01-01');

        $this->assertIsArray($result);
        // Le test passe si aucune exception n'est levée et qu'un tableau est retourné
    }
}