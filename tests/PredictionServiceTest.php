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
        $this->analyticsMock = $this->createMock(EnergyAnalyticsService::class);
        
        $this->weatherMock = $this->createMock(WeatherApiService::class);

        $this->predictionService = new PredictionService($this->analyticsMock);

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
        $this->analyticsMock->method('getPerformanceRatio')->willReturn(1.0);

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

        $this->assertEquals(950.0, $result['data'][0]['production']);
    }

    /**
     * Test : Vérifie le seuil de déclenchement éolien (doit être > 10 km/h).
     */
    public function testPredictWindThreshold(): void
    {
        $this->analyticsMock->method('getPerformanceRatio')->willReturn(10.0);

        $this->weatherMock->method('getHourlyWeather')->willReturn([
            ['date' => '2026-01-01 10:00:00', 'temp' => 10.0, 'sun' => 0.0, 'wind' => 5.0, 'rain' => 0.0],
            ['date' => '2026-01-01 11:00:00', 'temp' => 10.0, 'sun' => 0.0, 'wind' => 15.0, 'rain' => 0.0]
        ]);

        $result = $this->predictionService->predict('eolien', 'Bordeaux', '2026-01-01', '2026-01-01');

        $this->assertEquals(0.0, $result['data'][0]['production']);
        $this->assertEquals(150.0, $result['data'][1]['production']);
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

        $this->assertEquals(25.0, $result['data'][0]['production']);
    }

    /**
     * Test : Vérifie l'utilisation des ratios par défaut si l'historique est vide.
     */
    public function testPredictUsesDefaultRatioWhenNoHistory(): void
    {
        $this->analyticsMock->method('getPerformanceRatio')->willReturn(0.0);

        $this->weatherMock->method('getHourlyWeather')->willReturn([
            ['date' => '2026-01-01 10:00:00', 'temp' => 15.0, 'sun' => 100.0, 'wind' => 0.0, 'rain' => 0.0]
        ]);

        $result = $this->predictionService->predict('solaire', 'Lyon', '2026-01-01', '2026-01-01');

        $this->assertEquals(50.0, $result['data'][0]['production']);
    }
}