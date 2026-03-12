<?php
namespace Tests\Services;

use App\Services\EnergyAnalyticsService;
use App\Repositories\EnergyRepository;
use PHPUnit\Framework\TestCase;

class EnergyAnalyticsServiceTest extends TestCase {
    public function testGetPerformanceRatioCalculatesAverageCorrectly(): void {
        // On mock le repository
        $repoMock = $this->createMock(EnergyRepository::class);
        
        // On simule des données historiques
        $historicalData = [
            ['production' => 100, 'meteo' => 200], // ratio 0.5
            ['production' => 150, 'meteo' => 200], // ratio 0.75
        ];

        $repoMock->method('getHistoricalDataForRatio')
                 ->willReturn($historicalData);

        $service = new EnergyAnalyticsService($repoMock);
        $ratio = $service->getPerformanceRatio('solaire', 'Lyon');

        // (0.5 + 0.75) / 2 = 0.625
        $this->assertEquals(0.625, $ratio);
    }

    public function testGetPerformanceRatioReturnsZeroIfNoData(): void {
        $repoMock = $this->createMock(EnergyRepository::class);
        $repoMock->method('getHistoricalDataForRatio')->willReturn([]);

        $service = new EnergyAnalyticsService($repoMock);
        $this->assertEquals(0, $service->getPerformanceRatio('solaire', 'Paris'));
    }
}