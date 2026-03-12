<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use App\Services\DeepLearningPredictionService;

class DeepLearningPredictionServiceTest extends TestCase
{
    /**
     * Teste que le constructeur lève une exception si les tableaux de données 
     * d'entraînement n'ont pas la même longueur.
     */
    public function testConstructorThrowsExceptionOnMismatchedArrays(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All input arrays must have the same length');

        // Tableaux de tailles différentes
        new DeepLearningPredictionService(
            ['solaire', 'eolien'], // 2 éléments
            [20.5],               // 1 élément
            [100, 200],
            [50.0, 60.0]
        );
    }

    /**
     * Teste la capacité du service à générer des prédictions après entraînement.
     */
    public function testPredictReturnsFormattedData(): void
    {
        // Données d'entraînement (Archives)
        $meteoType = ['solaire', 'solaire', 'eolien', 'eolien'];
        $temp = [25.0, 26.0, 15.0, 14.0];
        $meteoData = [800.0, 850.0, 20.0, 22.0]; // Shortwave ou vent
        $archiveData = [50.5, 52.0, 30.0, 31.5];

        $service = new DeepLearningPredictionService($meteoType, $temp, $meteoData, $archiveData);

        // Données pour la prédiction (Futur)
        $predTypes = ['solaire', 'eolien'];
        $predTemps = [27.0, 13.0];
        $predMeteo = [900.0, 25.0];
        $predDates = ['2023-06-01 12:00:00', '2023-06-01 13:00:00'];
        $city = 'Montpellier';

        $predictions = $service->predict($predTypes, $predTemps, $predMeteo, $predDates, $city);

        // Vérifications de la structure de sortie
        $this->assertCount(2, $predictions);
        
        foreach ($predictions as $index => $row) {
            $this->assertEquals($predDates[$index], $row['date']);
            $this->assertEquals($city, $row['ville']);
            $this->assertEquals($predMeteo[$index], $row['meteo']);
            $this->assertEquals($predTemps[$index], $row['temp']);
            $this->assertEquals('prevision', $row['statut']);
            $this->assertEquals('lstm', $row['algo']);
            
            // Vérifie que la production est un float et qu'elle est arrondie
            $this->assertIsFloat($row['production']);
        }
    }

    /**
     * Teste la robustesse si aucune prédiction n'est générée ou si les données sont vides.
     */
    public function testPredictWithEmptyInputReturnsEmptyArray(): void
    {
        // Entraînement minimal
        $service = new DeepLearningPredictionService(['solaire'], [20], [100], [10]);

        $predictions = $service->predict([], [], [], [], 'Paris');

        $this->assertIsArray($predictions);
        $this->assertEmpty($predictions);
    }
}