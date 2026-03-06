<?php

namespace App\Controllers;

$mockMoveUploadedFile = true;
$mockUnlink = true;
$mockFileExists = null;

// Simule le déplacement du fichier
function move_uploaded_file(string $from, string $to): bool {
    global $mockMoveUploadedFile;
    return (bool)$mockMoveUploadedFile;
}

// Simule la suppression
function unlink(string $filename): bool {
    global $mockUnlink;
    return (bool)$mockUnlink;
}

// Simule l'existence d'un fichier (pour testDeleteSuccess)
function file_exists(string $filename): bool {
    global $mockFileExists;
    if ($mockFileExists !== null) {
        return (bool)$mockFileExists;
    }
    return \file_exists($filename);
}


namespace Tests\Controllers;

class EnergyControllerTest extends \PHPUnit\Framework\TestCase {
    private $controller;
    private $serviceMock;
    private string $tempUploadFile;

    protected function setUp(): void {
        global $mockMoveUploadedFile, $mockUnlink, $mockFileExists;
        $mockMoveUploadedFile = true;
        $mockUnlink = true;
        $mockFileExists = null;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_FILES = [];

        \App\Core\JsonResponse::$exitAfterSend = false;

        $this->serviceMock = $this->createMock(\App\Services\EnergyCsvService::class);

        $this->controller = $this->getMockBuilder(\App\Controllers\EnergyController::class)
            ->onlyMethods(['redirect', 'flash', 'requireLogin'])
            ->getMock();

        $reflection = new \ReflectionClass(\App\Controllers\EnergyController::class);
        $property = $reflection->getProperty('energyService');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->serviceMock);

        $this->tempUploadFile = sys_get_temp_dir() . '/test_upload.csv';
        file_put_contents($this->tempUploadFile, "date,valeur\n2023-01-01,100");
    }

    protected function tearDown(): void {
        if (file_exists($this->tempUploadFile)) {
            @unlink($this->tempUploadFile);
        }
    }

    /**
     * Test : Index renvoie le JSON combiné (CSV + Simulation IA).
     */
    public function testIndexReturnsCombinedData(): void {
        $_GET['type'] = 'solaire';
        $_GET['city'] = 'Paris';
        $_GET['from'] = '2023-01-01';
        $_GET['to']   = '2023-01-05'; 

        $csvData = [
            'data' => [
                ['date' => '2023-01-01 12:00', 'production' => 10],
                ['date' => '2023-01-02 12:00', 'production' => 15],
            ]
        ];
        
        $this->serviceMock->method('getEnergyData')->willReturn($csvData);

        $simData = [
            'data' => [
                ['date' => '2023-01-03 12:00', 'production' => 20, 'statut' => 'prevision'],
            ]
        ];

        $this->serviceMock->expects($this->once())
            ->method('simulateDataFromWeather')
            ->with('solaire', 'Paris', '2023-01-03', '2023-01-05')
            ->willReturn($simData);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $json = json_decode($output, true);
        $this->assertCount(3, $json['data']);
        $this->assertEquals(20, $json['data'][2]['production']);
    }

    /**
     * Test : Upload refuse une mauvaise extension.
     */
    public function testUploadFailsWithWrongExtension(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_FILES['csv_file'] = [
            'name' => 'image.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $this->tempUploadFile,
            'error' => 0,
            'size' => 123
        ];

        $this->controller->expects($this->once())
            ->method('flash')
            ->with('error', $this->stringContains('Format incorrect'));

        $this->controller->upload();
    }

    /**
     * Test : Upload réussit avec un bon CSV.
     */
    public function testUploadSuccess(): void {
        $_SESSION['user'] = ['id' => 42];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_FILES['csv_file'] = [
            'name' => 'data.csv',
            'type' => 'text/csv',
            'tmp_name' => $this->tempUploadFile,
            'error' => 0,
            'size' => 100
        ];

        global $mockMoveUploadedFile;
        $mockMoveUploadedFile = true;

        $this->controller->expects($this->once())
            ->method('flash')
            ->with('success');

        $this->controller->upload();
    }

    /**
     * Test : Delete supprime le fichier (Simulation réussie).
     */
    public function testDeleteSuccess(): void {
        global $mockFileExists, $mockUnlink;
        $mockFileExists = true;
        $mockUnlink = true;

        $_SESSION['user'] = ['id' => 42];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->controller->expects($this->once())
            ->method('flash')
            ->with('success');

        $this->controller->delete();
        
        $mockFileExists = null;
    }
}