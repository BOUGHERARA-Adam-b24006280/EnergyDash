<?php

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\CsvReader;

class CsvReaderTest extends TestCase {
    private string $tempFile;

    protected function tearDown(): void {
        if (isset($this->tempFile) && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    /**
     * Teste la lecture standard avec une virgule comme délimiteur.
     */
    public function testReadCsvWithCommaDelimiter(): void {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        $content = "Date,Value\n2023-01-01,100\n2023-01-02,200";
        file_put_contents($this->tempFile, $content);

        $reader = new CsvReader($this->tempFile);
        $rows = iterator_to_array($reader->getRows());

        $this->assertCount(2, $rows);
        
        $this->assertEquals('2023-01-01', $rows[0]['date']); 
        $this->assertEquals('100', $rows[0]['value']);
    }

    /**
     * Teste la détection automatique du point-virgule.
     */
    public function testDetectSemicolonDelimiter(): void {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        $content = "id;nom;email\n1;Adam;adam@example.com";
        file_put_contents($this->tempFile, $content);

        $reader = new CsvReader($this->tempFile);
        $rows = iterator_to_array($reader->getRows());

        $this->assertCount(1, $rows);
        $this->assertEquals('Adam', $rows[0]['nom']);
    }

    /**
     * Teste la suppression du BOM UTF-8 dans les en-têtes.
     */
    public function testCleanHeadersWithBom(): void {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        $bom = pack('H*', 'EFBBBF');
        $content = $bom . "Type,Source\nSolar,Panel";
        file_put_contents($this->tempFile, $content);

        $reader = new CsvReader($this->tempFile);
        $rows = iterator_to_array($reader->getRows());

        $firstRow = $rows[0];
        $this->assertArrayHasKey('type', $firstRow);
        $this->assertEquals('Solar', $firstRow['type']);
    }

    /**
     * Teste le comportement si le fichier n'existe pas.
     */
    public function testFileDoesNotExist(): void {
        $reader = new CsvReader('non_existent_file.csv');
        $rows = iterator_to_array($reader->getRows());

        $this->assertEmpty($rows);
    }

    /**
     * Teste qu'une ligne avec un nombre de colonnes incorrect est ignorée.
     */
    public function testIgnoreInvalidRowCount(): void {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        $content = "header1,header2\nval1,val2\ninvalid_row";
        file_put_contents($this->tempFile, $content);

        $reader = new CsvReader($this->tempFile);
        $rows = iterator_to_array($reader->getRows());

        $this->assertCount(1, $rows);
        $this->assertEquals('val2', $rows[0]['header2']);
    }
}