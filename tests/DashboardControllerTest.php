<?php

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\DashboardController;
use App\Services\EnergyCsvService;
use ReflectionClass;

class DashboardControllerTest extends TestCase
{
    private $controller;
    private $energyServiceMock;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        $this->energyServiceMock = $this->createMock(EnergyCsvService::class);

        $this->controller = $this->getMockBuilder(DashboardController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render', 'requireLogin'])
            ->getMock();

        $reflection = new ReflectionClass(DashboardController::class);
        $property = $reflection->getProperty('energyService');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->energyServiceMock);
    }

    /**
     * Test : index() récupère les villes et affiche la vue dashboard.
     */
    public function testIndexLoadsCitiesAndRendersView(): void
    {
        $fakeCities = ['Paris', 'Lyon', 'Marseille'];

        $this->energyServiceMock->expects($this->once())
            ->method('getAvailableCities')
            ->willReturn($fakeCities);

        $this->controller->expects($this->once())
            ->method('requireLogin');

        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'dashboard/dashboard',
                $this->callback(function ($data) use ($fakeCities) {
                    return isset($data['cities']) 
                        && $data['cities'] === $fakeCities
                        && $data['title'] === 'Tableau de bord - EnergyDash';
                })
            );

        $this->controller->index();
    }
}