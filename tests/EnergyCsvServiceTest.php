<?php

namespace App\Services;

$mockApiResponse = '';

function file_get_contents(string $filename, bool $use_include_path = false, $context = null): string|false {
    global $mockApiResponse;

    if (str_starts_with($filename, 'http')) {
        return $mockApiResponse;
    }

    return \file_get_contents($filename, $use_include_path, $context);
}

namespace Tests\Services;


class EnergyCsvServiceTest extends \PHPUnit\Framework\TestCase {
    private string $tempCsvPath;
    private \App\Services\EnergyCsvService $service;

    protected function setUp(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = ['user' => ['id' => 999, 'role' => 'user']];
        }

        $this->tempCsvPath = sys_get_temp_dir() . '/test_energy_' . uniqid() . '.csv';
        
        $csvContent = "type;ville;date_heure;production_kw;valeur_meteo;temperature_c\n" .
                      "solaire;Paris;01/01/2023 12:00;50.5;100;15\n" .
                      "eolien;Lyon;02/01/2023 14:00;80.0;20;10";
        
        file_put_contents($this->tempCsvPath, $csvContent);

        $this->service = new \App\Services\EnergyCsvService();

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('csvPath');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->tempCsvPath);
        
        $propDelim = $reflection->getProperty('delimiter');
        $propDelim->setAccessible(true);
        $propDelim->setValue($this->service, ';');
    }

    protected function tearDown(): void {
        if (file_exists($this->tempCsvPath)) {
            unlink($this->tempCsvPath);
        }
    }

    /**
     * Test : Récupération des villes disponibles dans le CSV.
     */
    public function testGetAvailableCitiesReturnsUniqueSortedCities(): void {
        $cities = $this->service->getAvailableCities();

        $this->assertIsArray($cities);
        $this->assertContains('Paris', $cities);
        $this->assertContains('Lyon', $cities);
        $this->assertCount(2, $cities);
    }

    /**
     * Test : getEnergyData filtre correctement par Type et Ville.
     */
    public function testGetEnergyDataFiltersCorrectly(): void {
        $result = $this->service->getEnergyData('solaire', 'Paris', '2023-01-01', '2023-12-31');

        $this->assertIsArray($result['data']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(50.5, $result['data'][0]['production']);
        $this->assertEquals('Paris', $result['data'][0]['ville']);
    }

    /**
     * Test : getEnergyData renvoie un tableau vide si aucune correspondance.
     */
    public function testGetEnergyDataReturnsEmptyIfNoMatch(): void {
        $result = $this->service->getEnergyData('hydraulique', 'Paris', '2023-01-01', '2023-12-31');

        $this->assertEmpty($result['data']);
    }

    /**
     * Test : Simulation API (IA/Prévision).
     * C'est ici qu'on utilise le Mock de file_get_contents.
     */
    public function testSimulateDataFromWeatherCallsApiAndReturnsData(): void {
        global $mockApiResponse;
        $mockApiResponse = json_encode([
            'hourly' => [
                'time' => ['2023-06-01T12:00', '2023-06-01T13:00'],
                'temperature_2m' => [25.5, 26.0],
                'precipitation' => [0.0, 0.0],
                'wind_speed_10m' => [10.0, 12.0],
                'shortwave_radiation' => [500.0, 600.0]
            ]
        ]);

        $result = $this->service->simulateDataFromWeather('solaire', 'Marseille', '2023-06-01', '2023-06-01');

        $this->assertEquals('solaire', $result['type']);
        $this->assertIsArray($result['data']);
        $this->assertCount(2, $result['data']);

        $this->assertGreaterThan(0, $result['data'][0]['production']);
        $this->assertEquals('prevision', $result['data'][0]['statut']);
    }

    /**
     * Test : Simulation API gère les erreurs (JSON vide ou invalide).
     */
    public function testSimulateDataHandlesApiError(): void {
        global $mockApiResponse;
        $mockApiResponse = false;

        $result = $this->service->simulateDataFromWeather('solaire', 'Paris', '2023-06-01', '2023-06-02');

        $this->assertEmpty($result['data']);
    }
}