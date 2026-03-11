<?php
/**
 * Fichier : CsvReader.php
 * Rôle : Responsable uniquement de la lecture technique et de l'extraction des données d'un fichier CSV.
 */

namespace App\Infrastructure;

/**
 * Classe responsable uniquement de la lecture technique d'un fichier CSV.
 */
class CsvReader {

    /** @var string Chemin absolu vers le fichier CSV cible. */
    private string $filePath;

    /** @var string Délimiteur de colonnes détecté (par défaut la virgule). */
    private string $delimiter = ',';

    /**
     * Initialise le chemin du fichier et lance la détection automatique du délimiteur.
     * @param string $filePath Le chemin complet vers le fichier à lire.
     */
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
        if (file_exists($this->filePath)) {
            $this->delimiter = $this->detectDelimiter($this->filePath);
        }
    }

    /**
     * Analyse le fichier pour détecter le séparateur de colonnes.
     * @param string $file Le chemin du fichier à analyser.
     * @return string Le délimiteur détecté (';' ou ',').
     */
    private function detectDelimiter(string $file): string {
        $handle = fopen($file, "r");
        if ($handle) {
            $line = fgets($handle);
            fclose($handle);
            if ($line !== false && substr_count($line, ';') > substr_count($line, ',')) return ';';
        }
        return ',';
    }

    /**
     * Nettoie et normalise les en-têtes du fichier CSV.
     * @param array<int, string|null> $headers Les en-têtes bruts extraits du fichier.
     * @return array<int, string> Les en-têtes nettoyés et normalisés.
     */
    private function cleanHeaders(array $headers): array {
    if (empty($headers)) return [];
    $headersString = array_map('strval', $headers);
    
    $bom = pack('H*', 'EFBBBF');
    if (str_starts_with($headersString[0], $bom)) {
        $headersString[0] = substr($headersString[0], strlen($bom));
    }
    
    return array_map(function($h) { 
        return strtolower(trim($h)); 
    }, $headersString);
}

    /**
     * Parcourt le fichier et retourne les lignes sous forme de tableaux associatifs.
     * @return \Generator Un itérateur produisant des tableaux [colonne => valeur].
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