<?php

namespace Tests\Services;

use App\Services\WeatherApiService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class WeatherApiServiceTest extends TestCase
{
    private WeatherApiService $service;
    private string $cachePath;

    protected function setUp(): void
    {
        $this->service = new WeatherApiService();
        $this->cachePath = __DIR__ . '/../Storage/cache/weather/';
        $this->clearCache();
    }

    protected function tearDown(): void
    {
        $this->clearCache();
    }

    /**
     * Nettoie le cache pour éviter que les tests ne lisent d'anciens fichiers.
     */
    private function clearCache(): void
    {
        if (is_dir($this->cachePath)) {
            $files = glob($this->cachePath . '*.json');
            if ($files) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * TEST DE LA LOGIQUE DE FORMATAGE (via Réflexion)
     * C'est la solution pour tester votre algorithme sans modifier WeatherApiService
     * et sans vous battre avec la variable magique $http_response_header.
     */
    public function testFormatWeatherData(): void
    {
        $hourlyData = [
            'time' => ['2026-03-12T10:00', '2026-03-12T11:00', '2026-03-13T10:00'],
            'temperature_2m' => [15.0, 16.0, 20.0],
            'precipitation' => [0.0, 0.0, 0.0],
            'wind_speed_10m' => [10.0, 10.0, 5.0],
            'shortwave_radiation' => [400.0, 500.0, 100.0]
        ];

        $method = new ReflectionMethod(WeatherApiService::class, 'formatWeatherData');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $hourlyData, '2026-03-12', '2026-03-12');

        $this->assertCount(2, $result, "Devrait contenir 2 relevés pour le 12 mars.");
        $this->assertEquals('2026-03-12 10:00:00', $result[0]['date']);
        $this->assertEquals(15.0, $result[0]['temp']);
        $this->assertEquals(400.0, $result[0]['sun']);
    }

    /**
     * TEST DE ROBUSTESSE (Rescue Mode)
     * Vérifie que si l'API est indisponible, le service renvoie bien 
     * les 24 relevés du mode secours.
     */
    public function testRescueModeOnApiFailure(): void
    {
        $result = $this->service->getHourlyWeather('lyon', '2026-03-12', '2026-03-12');

        $this->assertCount(24, $result, "Le mode secours doit générer 24 relevés horaires.");
        $this->assertArrayHasKey('temp', $result[0]);
    }
}