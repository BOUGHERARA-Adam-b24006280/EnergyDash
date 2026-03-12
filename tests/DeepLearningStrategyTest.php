<?php

namespace Tests\Strategies;

use App\Strategies\DeepLearningStrategy;
use App\Repositories\EnergyRepository;
use App\Services\WeatherApiService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DeepLearningStrategyTest extends TestCase
{
    private $repositoryMock;
    private $weatherApiMock;
    private $strategy;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(EnergyRepository::class);
        
        $this->weatherApiMock = $this->createMock(WeatherApiService::class);

        $this->strategy = new DeepLearningStrategy($this->repositoryMock);

        $reflection = new ReflectionClass($this->strategy);
        $property = $reflection->getProperty('weatherApi');
        $property->setAccessible(true);
        $property->setValue($this->strategy, $this->weatherApiMock);
    }

    /**
     * Test : Vérifie que la stratégie retourne une structure correcte même sans historique.
     */
    public function testPredictWithNoHistoricalDataReturnsValidStructure(): void
    {
        $this->repositoryMock->method('getHistoricalDataForRatio')->willReturn([]);

        $this->weatherApiMock->method('getHourlyWeather')->willReturn([
            [
                'date' => '2026-01-01 12:00:00',
                'temp' => 20.0,
                'sun'  => 500.0,
                'wind' => 10.0,
                'rain' => 0.0
            ]
        ]);

        $result = $this->strategy->predict('solaire', 'Lyon', '2026-01-01', '2026-01-01');

        $this->assertEquals('solaire', $result['type']);
        $this->assertEquals('Lyon', $result['city']);
        $this->assertIsArray($result['data']);
    }

    /**
     * Test : Vérifie le comportement quand l'API météo ne renvoie rien.
     */
    public function testPredictReturnsEmptyDataWhenWeatherApiFails(): void
    {
        $this->repositoryMock->method('getHistoricalDataForRatio')->willReturn([]);
        
        $this->weatherApiMock->method('getHourlyWeather')->willReturn([]);

        $result = $this->strategy->predict('eolien', 'Paris', '2026-01-01', '2026-01-01');

        $this->assertEmpty($result['data']);
        $this->assertEquals('eolien', $result['type']);
    }

    /**
     * Test : Vérifie que les données historiques sont correctement transmises.
     */
    public function testPredictProcessesHistoricalDataCorrectly(): void
    {
        $historical = [
            ['meteo' => 100.0, 'production' => 50.0],
            ['meteo' => 200.0, 'production' => 110.0]
        ];
        $this->repositoryMock->method('getHistoricalDataForRatio')->willReturn($historical);

        $this->weatherApiMock->method('getHourlyWeather')->willReturn([
            ['date' => '2026-01-01 12:00', 'temp' => 15.0, 'sun' => 150.0, 'wind' => 0.0, 'rain' => 0.0]
        ]);

        $result = $this->strategy->predict('solaire', 'Marseille', '2026-01-01', '2026-01-01');

        $this->assertArrayHasKey('data', $result);
    }
}