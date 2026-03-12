<?php

namespace App\Services;

// On initialise les variables avec des valeurs par défaut au cas où
$mockMoveUploadedFile = true;
$mockUnlink = true;
$mockFileExists = true;
$mockMimeType = 'text/csv';

function move_uploaded_file(string $from, string $to): bool {
    global $mockMoveUploadedFile;
    return $mockMoveUploadedFile ?? true;
}

function unlink(string $filename): bool {
    global $mockUnlink;
    return $mockUnlink ?? true;
}

function file_exists(string $filename): bool {
    global $mockFileExists;
    if ($mockFileExists === null) {
        return \file_exists($filename);
    }
    return $mockFileExists;
}

function finfo_open(int $flags = FILEINFO_NONE) {
    return "finfo_resource";
}

function finfo_file($finfo, string $filename): string|false {
    global $mockMimeType;
    return $mockMimeType ?? 'text/csv';
}

namespace Tests\Services;

use App\Services\FileUploadService;
use PHPUnit\Framework\TestCase;

class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;

    protected function setUp(): void
    {
        $this->service = new FileUploadService();
        
        global $mockMoveUploadedFile, $mockUnlink, $mockFileExists, $mockMimeType;
        $mockMoveUploadedFile = true;
        $mockUnlink = true;
        $mockFileExists = true;
        $mockMimeType = 'text/csv';
    }

    /**
     * Test : Un upload valide doit réussir.
     */
    public function testHandleCsvUploadSuccess(): void
    {
        $file = [
            'name' => 'data.csv',
            'tmp_name' => '/tmp/php123',
            'error' => UPLOAD_ERR_OK
        ];

        $this->service->handleCsvUpload($file, 42);
        $this->assertTrue(true); 
    }

    /**
     * Test : L'upload doit échouer si le code d'erreur PHP est présent.
     */
    public function testHandleCsvUploadFailsWithPhpError(): void
    {
        $file = [
            'name' => 'data.csv',
            'tmp_name' => '/tmp/php123',
            'error' => UPLOAD_ERR_INI_SIZE
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Erreur lors du transfert");

        $this->service->handleCsvUpload($file, 42);
    }

    /**
     * Test : L'upload doit rejeter les extensions autres que .csv.
     */
    public function testHandleCsvUploadRejectsInvalidExtension(): void
    {
        $file = [
            'name' => 'image.png',
            'tmp_name' => '/tmp/php123',
            'error' => UPLOAD_ERR_OK
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Format incorrect");

        $this->service->handleCsvUpload($file, 42);
    }

    /**
     * Test : L'upload doit rejeter un fichier dont le contenu n'est pas un CSV (MIME type).
     */
    public function testHandleCsvUploadRejectsInvalidMimeType(): void
    {
        global $mockMimeType;
        $mockMimeType = 'application/pdf';

        $file = [
            'name' => 'faux_fichier.csv',
            'tmp_name' => '/tmp/php123',
            'error' => UPLOAD_ERR_OK
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Fichier invalide détecté");

        $this->service->handleCsvUpload($file, 42);
    }

    /**
     * Test : Suppression réussie d'un fichier utilisateur.
     */
    public function testDeleteUserCsvSuccess(): void
    {
        global $mockFileExists, $mockUnlink;
        $mockFileExists = true;
        $mockUnlink = true;

        $result = $this->service->deleteUserCsv(42);

        $this->assertTrue($result);
    }

    /**
     * Test : Suppression échoue si le fichier n'existe pas.
     */
    public function testDeleteUserCsvFileNotFound(): void
    {
        global $mockFileExists;
        $mockFileExists = false;

        $result = $this->service->deleteUserCsv(999);

        $this->assertFalse($result);
    }
}