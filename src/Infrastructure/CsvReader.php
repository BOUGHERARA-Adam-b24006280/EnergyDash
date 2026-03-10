<?php
namespace App\Infrastructure;

/**
 * Classe responsable uniquement de la lecture technique d'un fichier CSV.
 */
class CsvReader {
    private string $filePath;
    private string $delimiter = ',';

    /**
     * @param string $filePath Le chemin absolu vers le fichier CSV.
     */
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
        if (file_exists($this->filePath)) {
            $this->delimiter = $this->detectDelimiter($this->filePath);
        }
    }

    private function detectDelimiter(string $file): string {
        $handle = fopen($file, "r");
        if ($handle) {
            $line = fgets($handle);
            fclose($handle);
            if ($line !== false && substr_count($line, ';') > substr_count($line, ',')) return ';';
        }
        return ',';
    }

    private function cleanHeaders(array $headers): array {
        if (empty($headers)) return [];
        $headersString = array_map('strval', $headers);
        
        // Suppression robuste du caractère invisible (BOM UTF-8)
        $bom = pack('H*', 'EFBBBF');
        if (str_starts_with($headersString[0], $bom)) {
            $headersString[0] = substr($headersString[0], strlen($bom));
        }
        
        return array_map(function($h) { return strtolower(trim($h)); }, $headersString);
    }

    /**
     * Lit le fichier ligne par ligne de manière optimisée (Générateur).
     * @return \Generator|array[] Un générateur produisant des tableaux associatifs [colonne => valeur].
     */
    public function getRows(): \Generator {
        if (!file_exists($this->filePath)) return;
        
        $handle = fopen($this->filePath, "r");
        if ($handle !== false) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            if ($rawHeaders !== false) {
                $headers = $this->cleanHeaders($rawHeaders);
                while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                    if (count($row) === count($headers)) {
                        yield array_combine($headers, $row);
                    }
                }
            }
            fclose($handle);
        }
    }
}