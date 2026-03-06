<?php

namespace Tests\Controllers;

class DashboardControllerTest extends \PHPUnit\Framework\TestCase {
    private $controller;
    private $energyServiceMock;
    private $viewMock;

    protected function setUp(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        $this->energyServiceMock = $this->createMock(\App\Services\EnergyCsvService::class);
        $this->viewMock = $this->createMock(\App\Core\View::class);

        $this->controller = $this->getMockBuilder(\App\Controllers\DashboardController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['requireLogin'])
            ->getMock();

        $reflection = new \ReflectionClass(\App\Controllers\DashboardController::class);

        $property = $reflection->getProperty('energyService');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->energyServiceMock);

        $property = $reflection->getProperty('view');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->viewMock);
    }

    /**
     * Test : index() récupère les villes et affiche la vue dashboard.
     */
    public function testIndexLoadsCitiesAndRendersView(): void {
        $fakeCities = ['Paris', 'Lyon', 'Marseille'];

        $this->energyServiceMock->expects($this->once())
            ->method('getAvailableCities')
            ->willReturn($fakeCities);

        $this->controller->expects($this->once())
            ->method('requireLogin');

        $this->viewMock->expects($this->once())
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