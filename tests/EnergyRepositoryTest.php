<?php

namespace Tests\Repositories;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\CsvReader;
use App\Repositories\EnergyRepository;

class EnergyRepositoryTest extends TestCase {
    
    /** @var \PHPUnit\Framework\MockObject\MockObject|CsvReader */
    private $csvReaderMock;
    private EnergyRepository $repository;

    protected function setUp(): void {
        // On crée un mock du CsvReader pour ne pas dépendre du système de fichiers
        $this->csvReaderMock = $this->createMock(CsvReader::class);
        $this->repository = new EnergyRepository($this->csvReaderMock);
    }

    /**
     * Fournit un jeu de données simulé pour le CsvReader.
     */
    private function mockCsvRows(array $rows): void {
        $this->csvReaderMock->method('getRows')->willReturn((function() use ($rows) {
            foreach ($rows as $row) {
                yield $row;
            }
        })());
    }

    /**
     * Teste la récupération des villes uniques et triées.
     */
    public function testGetAvailableCities(): void {
        $this->mockCsvRows([
            ['ville' => 'Paris'],
            ['ville' => ' Lyon '], // Teste le trim
            ['ville' => 'Paris'],   // Teste l'unicité
            ['ville' => 'Annecy']   // Teste le tri (A avant L et P)
        ]);

        $cities = $this->repository->getAvailableCities();

        $this->assertEquals(['Annecy', 'Lyon', 'Paris'], $cities);
    }

    /**
     * Teste la cartographie énergie/ville.
     */
    public function testGetCityEnergyMapping(): void {
        $this->mockCsvRows([
            ['ville' => 'Paris', 'type' => 'Solaire'],
            ['ville' => 'Paris', 'type' => 'eolien'],
            ['ville' => 'Lyon', 'type' => 'Solaire'],
        ]);

        $mapping = $this->repository->getCityEnergyMapping();

        $this->assertArrayHasKey('Paris', $mapping);
        $this->assertCount(2, $mapping['Paris']);
        $this->assertContains('solaire', $mapping['Paris']); // Vérifie la mise en minuscule
        $this->assertEquals(['solaire'], $mapping['Lyon']);
    }

    /**
     * Teste le filtrage principal par ville, type et date.
     */
    public function testGetEnergyDataFiltersCorrectly(): void {
        $this->mockCsvRows([
            [
                'type' => 'solaire',
                'ville' => 'Paris',
                'date_heure' => '2023/01/01 12:00',
                'production_kw' => '50',
                'valeur_meteo' => '100',
                'temperature_c' => '15'
            ],
            [
                'type' => 'eolien', // Mauvais type
                'ville' => 'Paris',
                'date_heure' => '2023/01/01 13:00'
            ],
            [
                'type' => 'solaire',
                'ville' => 'Lyon', // Mauvaise ville
                'date_heure' => '2023/01/01 14:00'
            ],
            [
                'type' => 'solaire',
                'ville' => 'Paris',
                'date_heure' => '2024/01/01 12:00' // Hors période
            ]
        ]);

        $results = $this->repository->getEnergyData('solaire', 'Paris', '2023-01-01', '2023-01-02');

        $this->assertCount(1, $results);
        $this->assertEquals('Paris', $results[0]['ville']);
        $this->assertEquals(50.0, $results[0]['production']);
        $this->assertEquals('reel', $results[0]['statut']);
    }

    /**
     * Teste la comparaison entre deux villes.
     */
    public function testGetEnergyDataWithComparison(): void {
        $this->mockCsvRows([
            ['type' => 'solaire', 'ville' => 'Paris', 'date_heure' => '2023/01/01 10:00'],
            ['type' => 'solaire', 'ville' => 'Lyon', 'date_heure' => '2023/01/01 11:00'],
            ['type' => 'solaire', 'ville' => 'Nice', 'date_heure' => '2023/01/01 12:00']
        ]);

        // On demande Paris, mais on ajoute Lyon en comparaison
        $results = $this->repository->getEnergyData('solaire', 'Paris', '2023-01-01', '2023-01-01', 'Lyon');

        $this->assertCount(2, $results);
        $this->assertEquals('Paris', $results[0]['ville']);
        $this->assertEquals('Lyon', $results[1]['ville']);
    }

    /**
     * Teste l'extraction des ratios historiques.
     */
    public function testGetHistoricalDataForRatio(): void {
        $this->mockCsvRows([
            ['type' => 'solaire', 'ville' => 'Paris', 'production_kw' => '10', 'valeur_meteo' => '0.5'],
            ['type' => 'solaire', 'ville' => 'Paris', 'production_kw' => '20', 'valeur_meteo' => '0.05'], // Météo trop faible (< 0.1)
            ['type' => 'eolien', 'ville' => 'Paris', 'production_kw' => '30', 'valeur_meteo' => '0.8']    // Mauvais type
        ]);

        $ratios = $this->repository->getHistoricalDataForRatio('solaire', 'Paris');

        $this->assertCount(1, $ratios);
        $this->assertEquals(10.0, $ratios[0]['production']);
        $this->assertEquals(0.5, $ratios[0]['meteo']);
    }
}