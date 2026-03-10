<?php
/**
 * Fichier : EnergyCsvService.php
 * Rôle : gère la lecture, le traitement et l'extraction des données historiques de production d'énergie depuis un fichier CSV.
 */

namespace App\Services;

/**
 * Service responsable de la manipulation et de l'extraction des données depuis les fichiers CSV d'énergie.
 */
class EnergyCsvService {

    /** @var string $csvPath Le chemin absolu vers le fichier CSV utilisé pour lire les données. */
    private string $csvPath;

    /** @var string $delimiter Le caratère utilisé pour séparer les colonnes dans le fichier CSV (virgule par défaut) */
    private string $delimiter = ','; 

    /**
     * Constructeur de service. Initialise le chemin du fichier CSV à utiliser.
     * @param int|null $userId L'ID de l'utilisateur (null pour utiliser le fichier par défaut)
     */
    public function __construct(?int $userId = null) {
        $idSuffix = $userId ?? 'default';
        $userFile = __DIR__ . '/../../Storage/energy_user_' . $idSuffix . '.csv';
        $defaultFile = __DIR__ . '/../../Storage/energyData.csv';

        if ($userId && file_exists($userFile)) {
            $this->csvPath = $userFile;
        } else {
            $this->csvPath = $defaultFile;
        }
        
        if (file_exists($this->csvPath)) {
            $this->delimiter = $this->detectDelimiter($this->csvPath);
        }
    }

    /** 
     * Détecte automatiquement le délimiteur utilisé dans le fichier CSV (virgule ou point virgule).
     * @param string $file Le chemin absolu vers le fichier CSV à analyser.
     * @return string Le déliimiteur détecté (',' ou ';').
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
     * Nettoie les en-têtes du fichier CSV (supprime les BOM UTF-8, met en minuscules et retire les espaces).
     * @param array $headers Le tableau brut des en-têtes récupéré via fgetcsv.
     * @return array Le tableau des en-têtes nettoyés.
     */
    private function cleanHeaders(array $headers): array {
        if (empty($headers)) return [];
        $headersString = array_map('strval', $headers);
        $bom = pack('H*','EFBBBF');
        $first = preg_replace("/^$bom/", '', $headersString[0]);
        $headersString[0] = $first ?? $headersString[0];
        
        return array_map(function($h) {
            return strtolower(trim($h));
        }, $headersString);
    }

    /**
     * Récupère un tableau associatif liant chaque ville à ses types d'énergie disponibles.
     * @return array Un tableau sous la forme ['ville' => ['type1', 'type2']].
     */
    public function getCityEnergyMapping(): array {
        if (!file_exists($this->csvPath)) return [];
        $mapping = [];
        
        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            if ($rawHeaders !== false) {
                $headers = $this->cleanHeaders($rawHeaders);
                $cityIndex = array_search('ville', $headers);
                $typeIndex = array_search('type', $headers);

                if ($cityIndex !== false && $typeIndex !== false) {
                    while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                        if (isset($row[$cityIndex]) && isset($row[$typeIndex])) {
                            $city = trim((string)$row[$cityIndex]);
                            $type = strtolower(trim((string)$row[$typeIndex]));
                            
                            if (!isset($mapping[$city])) $mapping[$city] = [];
                            if (!in_array($type, $mapping[$city])) $mapping[$city][] = $type;
                        }
                    }
                }
            }
            fclose($handle);
        }
        ksort($mapping);
        return $mapping;
    }

    /**
     * Récupère la liste de toutes les villes disponibles dans le fichier CSV.
     * @return array Un tableau indexé contenant le nom des villes triées par ordre alphabétique.
     */
    public function getAvailableCities(): array {
        if (!file_exists($this->csvPath)) return [];
        $cities = [];
        
        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            if ($rawHeaders !== false) {
                $headers = $this->cleanHeaders($rawHeaders);
                $cityIndex = array_search('ville', $headers);

                if ($cityIndex !== false) {
                    while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                        if (isset($row[$cityIndex])) {
                            $cities[] = trim((string)$row[$cityIndex]);
                        }
                    }
                }
            }
            fclose($handle);
        }
        $unique = array_unique($cities);
        sort($unique);
        return $unique;
    }

    /**
     * Récupère les données de production énergétique réelles pour des critères spécifiques.
     * @param string $type Le type d'énergie.
     * @param string $city La ville ciblée (ou 'all').
     * @param string $from La date de début de la recherche au format 'Y-m-d'.
     * @param string $to La date de fin de la recherche au format 'Y-m-d'.
     * @param string|null $compareCity (Optionnel) Une deuxième ville pour effectuer une comparaison.
     * @return array Les données formatées et filtrées contenant les productions réelles heure par heure.
     */
    public function getEnergyData(string $type, string $city, string $from, string $to, ?string $compareCity = null): array {
        if (!file_exists($this->csvPath)) return $this->fmt($type, $city, $from, $to, []);

        $results = [];
        
        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            if ($rawHeaders !== false) {
                $headers = $this->cleanHeaders($rawHeaders);

                while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                    if (count($row) !== count($headers)) continue;
                    $data = array_combine($headers, $row);

                    $dataType = isset($data['type']) ? strtolower(trim((string)$data['type'])) : '';
                    $dataVille = isset($data['ville']) ? strtolower(trim((string)$data['ville'])) : '';

                    if ($type !== 'all' && $dataType !== strtolower($type)) continue;

                    $targetCity = strtolower($city);
                    $compCity = $compareCity ? strtolower($compareCity) : null;

                    if ($city !== 'all') {
                        if ($dataVille !== $targetCity && $dataVille !== $compCity) continue;
                    }

                    if (isset($data['date_heure'])) {
                        $cleanDate = str_replace('/', '-', (string)$data['date_heure']);
                        $ts = strtotime($cleanDate);
                        if ($ts === false) continue;

                        $d = date('Y-m-d', $ts);
                        
                        if ($d >= $from && $d <= $to) {
                            $results[] = [
                                'date' => date('Y-m-d H:00:00', $ts),
                                'production' => (float)($data['production_kw'] ?? 0),
                                'ville' => (string)($data['ville'] ?? ''),
                                'meteo' => (float)($data['valeur_meteo'] ?? 0),
                                'temp'  => (float)($data['temperature_c'] ?? 0),
                                'statut'=> 'reel'
                            ];
                        }
                    }
                }
            }
            fclose($handle);
        }
        
        usort($results, function($a, $b) {
            $tA = strtotime(str_replace('/', '-', (string)$a['date']));
            $tB = strtotime(str_replace('/', '-', (string)$b['date']));
            return ($tA ?: 0) - ($tB ?: 0);
        });

        return $this->fmt($type, $city, $from, $to, $results);
    }

    /**
     * Calcule le ratio de performance historique moyen (Production / Météo).
     * @param string $type Le type d'énergie concerné.
     * @param string $city La ville concernée.
     * @return float Le ratio moyen de performance calculé sur la base de l'historique.
     */
    public function getPerformanceRatio(string $type, string $city): float {
        if (!file_exists($this->csvPath)) return 0;
        $totalRatio = 0; $count = 0;

        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            if ($rawHeaders !== false) {
                $headers = $this->cleanHeaders($rawHeaders);
                while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                    if (count($row) !== count($headers)) continue;
                    $data = array_combine($headers, $row);

                    $dataType = strtolower((string)($data['type'] ?? ''));
                    $dataVille = strtolower((string)($data['ville'] ?? ''));
                    $dataMeteo = (float)($data['valeur_meteo'] ?? 0);

                    if ($dataType === strtolower($type) && $dataVille === strtolower($city) && $dataMeteo > 10) { 
                        $prod = (float)($data['production_kw'] ?? 0);
                        $totalRatio += ($prod / $dataMeteo);
                        $count++;
                    }
                }
            }
            fclose($handle);
        }
        return ($count > 0) ? $totalRatio / $count : 0;
    }

    /**
     * Formate la structure de réponse finale renvoyée par le service de données.
     * @param string $type Le type d'énergie demandé.
     * @param string $city La ville demandée.
     * @param string $from La date de début de la plage.
     * @param string $to La date de fin de la plage.
     * @param array $data Le tableau des résultats horaires formatés de production.
     * @return array Le tableau associatif final encapsulant les méta-données et les résultats.
     */
    private function fmt($type, $city, $from, $to, $data): array {
        return ['type' => $type, 'city' => $city, 'from' => $from, 'to' => $to, 'data' => $data];
    }
}