<?php

namespace Tests\Services;

use App\Services\PredictionService;
use App\Services\EnergyAnalyticsService;
use App\Services\WeatherApiService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PredictionServiceTest extends TestCase
{
    private $analyticsMock;
    private $weatherMock;
    private $predictionService;

    protected function setUp(): void
    {
        // Mock du service d'analyse historique
        $this->analyticsMock = $this->createMock(EnergyAnalyticsService::class);
        
        // Mock du service API Météo pour éviter les appels réseau réels
        $this->weatherMock = $this->createMock(WeatherApiService::class);

        // Initialisation du service à tester
        $this->predictionService = new PredictionService($this->analyticsMock);

        // Injection du mock WeatherApiService via Reflection (car privé et créé par "new")
        $reflection = new ReflectionClass($this->predictionService);
        $property = $reflection->getProperty('weatherApi');
        $property->setAccessible(true);
        $property->setValue($this->predictionService, $this->weatherMock);
    }

    /**
     * Test : Vérifie le calcul solaire avec une pénalité de température (> 25°C).
     */
    public function testPredictSolarWithHighTemperature(): void
    {
        // On définit un ratio de performance de 1.0 pour simplifier
        $this->analyticsMock->method('getPerformanceRatio')->willReturn(1.0);

        // Simulation de données météo : 1000 W/m2 de soleil et 30°C
        $this->weatherMock->method('getHourlyWeather')->willReturn([
            [
                'date' => '2026-07-01 12:00:00',
                'temp' => 30.0,
                'sun'  => 1000.0,
                'wind' => 10.0,
                'rain' => 0.0
            ]
        ]);

        $result = $this->predictionService->predict('solaire', 'Nice', '2026-07-01', '2026-07-01');

        // Calcul attendu : 1000 (soleil) * 1.0 (ratio) * 0.95 (pénalité > 25°C) = 950
        $this->assertEquals(950.0, $result['data'][0]['production']);
    }

    /**
     * Test : Vérifie le seuil de déclenchement éolien (doit être > 10 km/h).
     */
    public function testPredictWindThreshold(): void
    {
        $this->analyticsMock->method('getPerformanceRatio')->willReturn(10.0);

        // Cas 1 : Vent trop faible (5 km/h) -> Production 0
        // Cas 2 : Vent suffisant (15 km/h) -> Production
        $this->weatherMock->method('getHourlyWeather')->willReturn([
            ['date' => '2026-01-01 10:00:00', 'temp' => 10.0, 'sun' => 0.0, 'wind' => 5.0, 'rain' => 0.0],
            ['date' => '2026-01-01 11:00:00', 'temp' => 10.0, 'sun' => 0.0, 'wind' => 15.0, 'rain' => 0.0]
        ]);

        $result = $this->predictionService->predict('eolien', 'Bordeaux', '2026-01-01', '2026-01-01');

        $this->assertEquals(0.0, $result['data'][0]['production']); // 5 km/h < 10
        $this->assertEquals(150.0, $result['data'][1]['production']); // 15 km/h * ratio 10 = 150
    }

    /**
     * Test : Vérifie le calcul hydraulique (formule spécifique).
     */
    public function testPredictHydraulic(): void
    {
        $this->analyticsMock->method('getPerformanceRatio')->willReturn(1.0);

        $this->weatherMock->method('getHourlyWeather')->willReturn([
            ['date' => '2026-01-01 10:00:00', 'temp' => 10.0, 'sun' => 0.0, 'wind' => 0.0, 'rain' => 2.0]
        ]);

        $result = $this->predictionService->predict('hydraulique', 'Grenoble', '2026-01-01', '2026-01-01');

        // Formule hydraulique : 5.0 + (pluie * ratio * 10)
        // 5.0 + (2.0 * 1.0 * 10) = 25.0
        $this->assertEquals(25.0, $result['data'][0]['production']);
    }

    /**
     * Test : Vérifie l'utilisation des ratios par défaut si l'historique est vide.
     */
    public function testPredictUsesDefaultRatioWhenNoHistory(): void
    {
        // On simule l'absence de données historiques (ratio 0)
        $this->analyticsMock->method('getPerformanceRatio')->willReturn(0.0);

        $this->weatherMock->method('getHourlyWeather')->willReturn([
            ['date' => '2026-01-01 10:00:00', 'temp' => 15.0, 'sun' => 100.0, 'wind' => 0.0, 'rain' => 0.0]
        ]);

        $result = $this->predictionService->predict('solaire', 'Lyon', '2026-01-01', '2026-01-01');

        // Ratio par défaut solaire = 0.5
        // 100 (sun) * 0.5 = 50.0
        $this->assertEquals(50.0, $result['data'][0]['production']);
    }
}