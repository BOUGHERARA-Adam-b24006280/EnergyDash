<?php
namespace App\Services;

/**
 * Classe EnergyCsvService
 * Service centralisant la gestion des données CSV et la simulation IA via Open-Meteo.
 * Fusionne la logique métier de la branche "Prévision" avec l'architecture "Amélioration du Code".
 */
class EnergyCsvService {
    /** @var string Chemin vers le fichier CSV actuellement utilisé */
    private string $csvPath;
    
    /** @var string Délimiteur CSV détecté (',' ou ';') */
    private string $delimiter = ','; 

    /**
     * Constructeur.
     * Initialise la session si besoin, détermine le fichier CSV de l'utilisateur
     * et détecte automatiquement le séparateur.
     */
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        $userId = null;
        $userRole = 'user';

        if (is_array($user)) {
            $userId = (isset($user['id']) && is_scalar($user['id'])) ? (string)$user['id'] : null;
            $userRole = (isset($user['role']) && is_string($user['role'])) ? $user['role'] : 'user';
        }

        // Chemins des fichiers
        $idSuffix = $userId ?? 'default';
        $userFile = __DIR__ . '/../../Storage/energy_user_' . $idSuffix . '.csv';
        $defaultFile = __DIR__ . '/../../Storage/energyData.csv';

        if ($userId && file_exists($userFile)) {
            $this->csvPath = $userFile;
        } else {
            $this->csvPath = $defaultFile;
        }
        
        // Détection séparateur
        if (file_exists($this->csvPath)) {
            $this->delimiter = $this->detectDelimiter($this->csvPath);
        }
    }

    /**
     * Helper : Détecte si le fichier utilise des virgules ou des points-virgules.
     */
    private function detectDelimiter(string $file): string {
        $handle = fopen($file, "r");
        if ($handle) {
            $line = fgets($handle); // Lit la première ligne
            fclose($handle);
            if ($line !== false && substr_count($line, ';') > substr_count($line, ',')) {
                return ';';
            }
        }
        return ',';
    }

    /**
     * Helper : Nettoie les en-têtes CSV.
     * 
     * @param array<int, string> $headers
     * @return array<int, string>
     */
    private function cleanHeaders(array $headers): array {
        if (empty($headers)) {
            return [];
        }
        
        // On convertit tout en string pour rassurer PHPStan (list<string|null> -> array<string>)
        $headersString = array_map('strval', $headers);

        $bom = pack('H*','EFBBBF');
        $first = preg_replace("/^$bom/", '', $headersString[0]);
        $headersString[0] = $first ?? $headersString[0];
        
        return array_map(function($h) {
            return strtolower(trim($h));
        }, $headersString);
    }

    /**
     * Récupère la liste des villes (utile pour les filtres).
     * @return array<int, string>
     */
    public function getAvailableCities(): array{
        if (!file_exists($this->csvPath)) return [];
        $cities = [];
        
        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            
            // On vérifie simplement que ce n'est pas false.
            if ($rawHeaders !== false) {
                $headers = $this->cleanHeaders($rawHeaders);
                $cityIndex = array_search('ville', $headers);

                if ($cityIndex !== false) {
                    while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                        // row est array|false|null.
                        if (is_array($row) && isset($row[$cityIndex])) {
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
     * Récupère les données historiques depuis le CSV avec filtrage.
     * @return array<string, mixed>
     */
    public function getEnergyData(string $type, string $city, string $from, string $to, ?string $compareCity = null): array{
        if (!file_exists($this->csvPath)) return $this->fmt($type, $city, $from, $to, []);

        $results = [];
        
        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            
            if ($rawHeaders !== false) {
                $headers = $this->cleanHeaders($rawHeaders);

                while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                    // Si pas un tableau ou nombre de colonnes incorrect, on saute
                    if (!is_array($row) || count($row) !== count($headers)) continue;
                    
                    // PHPStan sait que count est égal, array_combine renverra un tableau
                    $data = array_combine($headers, $row);

                    $dataType = isset($data['type']) ? strtolower(trim((string)$data['type'])) : '';
                    $dataVille = isset($data['ville']) ? strtolower(trim((string)$data['ville'])) : '';

                    if ($type !== 'all' && $dataType !== strtolower($type)) {
                        continue;
                    }

                    $targetCity = strtolower($city);
                    $compCity = $compareCity ? strtolower($compareCity) : null;

                    if ($city !== 'all') {
                        if ($dataVille !== $targetCity && $dataVille !== $compCity) {
                            continue;
                        }
                    }

                    if (isset($data['date_heure'])) {
                        $cleanDate = str_replace('/', '-', (string)$data['date_heure']);
                        $ts = strtotime($cleanDate);
                        if ($ts === false) continue;

                        $d = date('Y-m-d', $ts);
                        
                        if ($d >= $from && $d <= $to) {
                            $results[] = [
                                'date' => (string)$data['date_heure'],
                                'production' => (float)($data['production_kw'] ?? 0),
                                'ville' => (string)($data['ville'] ?? ''),
                                'meteo'      => (float)($data['valeur_meteo'] ?? 0),
                                'temp'       => (float)($data['temperature_c'] ?? 0)
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
     * Calcule l'efficacité de l'installation (Ratio Historique).
     * Utilisé par le simulateur pour calibrer les prédictions selon l'historique de l'utilisateur.
     */
    private function getPerformanceRatio(string $type, string $city): float {
        if (!file_exists($this->csvPath)) return 0;

        $totalRatio = 0;
        $count = 0;

        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            if ($rawHeaders !== false) {
                $headers = $this->cleanHeaders($rawHeaders);
                
                while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                    if (!is_array($row) || count($row) !== count($headers)) continue;
                    $data = array_combine($headers, $row);

                    $dataType = strtolower((string)($data['type'] ?? ''));
                    $dataVille = strtolower((string)($data['ville'] ?? ''));
                    $dataMeteo = (float)($data['valeur_meteo'] ?? 0);

                    if ($dataType === strtolower($type) && 
                        $dataVille === strtolower($city) &&
                        $dataMeteo > 10) { 
                        
                        $prod = (float)($data['production_kw'] ?? 0);
                        
                        if ($dataMeteo > 0) {
                            $totalRatio += ($prod / $dataMeteo);
                            $count++;
                        }
                    }
                }
            }
            fclose($handle);
        }
        return ($count > 0) ? $totalRatio / $count : 0;
    }

    /**
     * SIMULATEUR HYBRIDE (Fonctionnalité clé)
     * Utilise Open-Meteo pour générer des données futures (Forecast) ou passées (Archive)
     * quand le CSV s'arrête.
     * @return array<string, mixed>
     */
    public function simulateDataFromWeather(string $type, string $city, string $startDate, string $endDate): array {
        $coordinates = [
            'lyon' =>      ['lat' => 45.76, 'lon' => 4.83],
            'paris' =>     ['lat' => 48.85, 'lon' => 2.35],
            'marseille' => ['lat' => 43.29, 'lon' => 5.36],
            'nice' =>      ['lat' => 43.71, 'lon' => 7.26],
            'grenoble' =>  ['lat' => 45.18, 'lon' => 5.72],
            'bordeaux' =>  ['lat' => 44.83, 'lon' => -0.57]
        ];
        $cityKey = strtolower($city);
        $coords = $coordinates[$cityKey] ?? $coordinates['lyon'];

        $performanceRatio = $this->getPerformanceRatio($type, $city);
        
        if ($performanceRatio <= 0) {
            if ($type === 'solaire') $performanceRatio = 0.005; 
            elseif ($type === 'eolien') $performanceRatio = 0.1; 
            else $performanceRatio = 0.5;
        }

        $today = date('Y-m-d');

        if ($endDate < $today) {
            $apiUrl = "https://archive-api.open-meteo.com/v1/archive?latitude={$coords['lat']}&longitude={$coords['lon']}&start_date={$startDate}&end_date={$endDate}&hourly=temperature_2m,precipitation,wind_speed_10m,shortwave_radiation&timezone=Europe%2FParis";
        } else {
            $apiUrl = "https://api.open-meteo.com/v1/forecast?latitude={$coords['lat']}&longitude={$coords['lon']}&start_date={$startDate}&end_date={$endDate}&hourly=temperature_2m,precipitation,wind_speed_10m,shortwave_radiation&timezone=Europe%2FParis";
        }
        
        // Permet de faire fonctionner l'appel API même en local (WAMP/XAMPP) sans certificat
        $context = stream_context_create([
            "ssl" => ["verify_peer" => false, "verify_peer_name" => false],
            "http" => ["timeout" => 5]
        ]);

        $json = @file_get_contents($apiUrl, false, $context);
        
        if ($json === false) return $this->fmt($type, $city, $startDate, $endDate, []);

        $apiData = json_decode($json, true);

        if (!is_array($apiData) || !isset($apiData['hourly']) || !is_array($apiData['hourly'])) {
            return $this->fmt($type, $city, $startDate, $endDate, []);
        }

        $hourly = $apiData['hourly'] ?? [];
        $predictions = [];

        if (isset($hourly['time'])) {
            foreach ($hourly['time'] as $index => $isoDate) {
                $dateString = str_replace('T', ' ', $isoDate) . ':00';
                $dayOnly = substr($dateString, 0, 10); 
                
                if ($dayOnly < $startDate || $dayOnly > $endDate) continue;

                $temp = isset($hourly['temperature_2m'][$index]) ? (float)$hourly['temperature_2m'][$index] : 0.0;
                $rain = isset($hourly['precipitation'][$index]) ? (float)$hourly['precipitation'][$index] : 0.0;
                $wind = isset($hourly['wind_speed_10m'][$index]) ? (float)$hourly['wind_speed_10m'][$index] : 0.0;
                $sun  = isset($hourly['shortwave_radiation'][$index]) ? (float)$hourly['shortwave_radiation'][$index] : 0.0;

                $predictedProd = 0;
                $meteoValueForChart = 0;

                if ($type === 'solaire') {
                    $predictedProd = $sun * $performanceRatio;
                    if ($temp > 25) $predictedProd *= 0.95; // Malus chaleur
                    $meteoValueForChart = $sun;
                } elseif ($type === 'eolien') {
                    if ($wind > 10) $predictedProd = $wind * $performanceRatio; // Seuil démarrage
                    $meteoValueForChart = $wind;
                } elseif ($type === 'hydraulique') {
                    $predictedProd = 5.0 + ($rain * $performanceRatio * 10);
                    $meteoValueForChart = $rain;
                }

                $predictions[] = [
                    'date' => $dateString,
                    'production' => round($predictedProd, 2),
                    'ville' => $city,
                    'meteo' => $meteoValueForChart,
                    'temp' => $temp,
                    'statut' => 'prevision'
                ];
            }
        }
        return $this->fmt($type, $city, $startDate, $endDate, $predictions);
    }

    /**
     * Helper : Formate la réponse standardisée.
     * @param string $type
     * @param string $city
     * @param string $from
     * @param string $to
     * @param array<int, array<string, mixed>> $data
     * @return array<string, mixed>
     */
    private function fmt($type, $city, $from, $to, $data): array {
        return ['type' => $type, 'city' => $city, 'from' => $from, 'to' => $to, 'data' => $data];
    }
}