<?php

namespace Tests\Controllers;

use App\Controllers\DashboardController;
use App\Repositories\EnergyRepository;
use App\Core\View;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DashboardControllerTest extends TestCase {
    private $controller;
    private $energyRepoMock;
    private $viewMock;

    protected function setUp(): void {
        // Initialisation de la session pour les tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $_SESSION['csrf_token'] = 'test_token_123';

        // Création des mocks pour les nouvelles dépendances
        $this->energyRepoMock = $this->createMock(EnergyRepository::class);
        $this->viewMock = $this->createMock(View::class);

        // On mocke le contrôleur en désactivant le constructeur (pour éviter les accès fichiers réels)
        $this->controller = $this->getMockBuilder(DashboardController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['requireLogin', 'initCsrf'])
            ->getMock();

        // Injection des mocks dans les propriétés privées via Reflection
        $reflection = new ReflectionClass(DashboardController::class);

        $repoProp = $reflection->getProperty('energyRepository');
        $repoProp->setAccessible(true);
        $repoProp->setValue($this->controller, $this->energyRepoMock);

        $viewProp = $reflection->getProperty('view');
        $viewProp->setAccessible(true);
        $viewProp->setValue($this->controller, $this->viewMock);
    }

    /**
     * Test : index() récupère les données et affiche la vue dashboard avec les bons paramètres.
     */
    public function testIndexLoadsDataAndRendersView(): void {
        // 1. Préparation des données fictives (Données attendues du Repository)
        $fakeCities = ['Paris', 'Lyon', 'Bordeaux'];
        $fakeMapping = [
            'Paris' => ['solaire', 'eolien'],
            'Lyon'  => ['solaire']
        ];

        // 2. Configuration des attentes sur le Repository
        $this->energyRepoMock->expects($this->once())
            ->method('getAvailableCities')
            ->willReturn($fakeCities);

        $this->energyRepoMock->expects($this->once())
            ->method('getCityEnergyMapping')
            ->willReturn($fakeMapping);

        // 3. Vérification de l'appel aux méthodes de sécurité
        $this->controller->expects($this->once())->method('requireLogin');
        $this->controller->expects($this->once())->method('initCsrf');

        // 4. Vérification du rendu de la vue avec les bonnes données
        $this->viewMock->expects($this->once())
            ->method('render')
            ->with(
                'dashboard/dashboard',
                $this->callback(function ($data) use ($fakeCities, $fakeMapping) {
                    return $data['title'] === 'Tableau de bord - EnergyDash'
                        && $data['cities'] === $fakeCities
                        && $data['energyMapping'] === $fakeMapping
                        && $data['csrf_token'] === 'test_token_123';
                })
            );

        // Exécution de la méthode
        $this->controller->index();
    }
}