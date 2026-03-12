<?php
namespace Tests\Services;

use App\Services\EnergyAnalyticsService;
use App\Repositories\EnergyRepository;
use PHPUnit\Framework\TestCase;

class EnergyAnalyticsServiceTest extends TestCase {
    public function testGetPerformanceRatioCalculatesAverageCorrectly(): void {
        $repoMock = $this->createMock(EnergyRepository::class);
        
        $historicalData = [
            ['production' => 100, 'meteo' => 200],
            ['production' => 150, 'meteo' => 200],
        ];

        $repoMock->method('getHistoricalDataForRatio')
                 ->willReturn($historicalData);

        $service = new EnergyAnalyticsService($repoMock);
        $ratio = $service->getPerformanceRatio('solaire', 'Lyon');

        $this->assertEquals(0.625, $ratio);
    }

    public function testGetPerformanceRatioReturnsZeroIfNoData(): void {
        $repoMock = $this->createMock(EnergyRepository::class);
        $repoMock->method('getHistoricalDataForRatio')->willReturn([]);

        $service = new EnergyAnalyticsService($repoMock);
        $this->assertEquals(0, $service->getPerformanceRatio('solaire', 'Paris'));
    }
}