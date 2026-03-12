<?php

namespace Tests\Factories;

use PHPUnit\Framework\TestCase;
use App\Factories\PredictionFactory;
use App\Repositories\EnergyRepository;
use App\Services\EnergyAnalyticsService;
use App\Services\PredictionService;
use App\Strategies\DeepLearningStrategy;

class PredictionFactoryTest extends TestCase {
    private string $algoFilePath;
    private $repositoryMock;
    private $analyticsMock;

    protected function setUp(): void {
        // CORRECTION : Le chemin doit remonter d'un seul niveau depuis 'tests/' 
        // pour atteindre la racine du projet où se trouve 'Storage/'
        $this->algoFilePath = dirname(__DIR__) . '/Storage/active_algo.txt';
        
        if (!is_dir(dirname($this->algoFilePath))) {
            mkdir(dirname($this->algoFilePath), 0777, true);
        }

        $this->repositoryMock = $this->createMock(EnergyRepository::class);
        $this->analyticsMock = $this->createMock(EnergyAnalyticsService::class);
    }

    protected function tearDown(): void {
        if (file_exists($this->algoFilePath)) {
            unlink($this->algoFilePath);
        }
    }

    /**
     * Teste que la factory retourne PredictionService par défaut (quand le fichier n'existe pas).
     */
    public function testMakeReturnsStandardServiceByDefault(): void {
        // On s'assure que le fichier est bien supprimé avant l'appel
        if (file_exists($this->algoFilePath)) {
            unlink($this->algoFilePath);
        }

        $strategy = PredictionFactory::make($this->repositoryMock, $this->analyticsMock);

        $this->assertInstanceOf(PredictionService::class, $strategy);
    }

    /**
     * Teste que la factory retourne DeepLearningStrategy quand 'lstm' est configuré.
     */
    public function testMakeReturnsDeepLearningStrategyForLstm(): void {
        file_put_contents($this->algoFilePath, 'lstm');

        $strategy = PredictionFactory::make($this->repositoryMock, $this->analyticsMock);

        $this->assertInstanceOf(DeepLearningStrategy::class, $strategy);
    }

    /**
     * Teste que la factory retourne PredictionService pour toute autre valeur.
     */
    public function testMakeReturnsStandardServiceForOtherValues(): void {
        file_put_contents($this->algoFilePath, 'unknown_algo');

        $strategy = PredictionFactory::make($this->repositoryMock, $this->analyticsMock);

        $this->assertInstanceOf(PredictionService::class, $strategy);
    }

    /**
     * Teste la robustesse si le fichier contient des espaces ou des retours à la ligne.
     */
    public function testMakeHandlesWhitespaceInFile(): void {
        file_put_contents($this->algoFilePath, "  lstm  \n");

        $strategy = PredictionFactory::make($this->repositoryMock, $this->analyticsMock);

        $this->assertInstanceOf(DeepLearningStrategy::class, $strategy);
    }
}