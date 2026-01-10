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
        // --- CORRECTIF 1 : SÉCURITÉ SESSION ---
        // Indispensable car on accède à $_SESSION['user']. 
        // Si la session n'est pas démarrée ailleurs, le service plantera sans ceci.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;
        $userRole = $_SESSION['user']['role'] ?? 'user';

        // Chemins des fichiers
        $userFile = __DIR__ . '/../../Storage/energy_user_' . $userId . '.csv';
        $defaultFile = __DIR__ . '/../../Storage/energyData.csv';

        // Seuls les admins/éditeurs ou les utilisateurs ayant uploadé un fichier utilisent le fichier perso
        $canUpload = in_array($userRole, ['admin', 'editor']);

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
            if (substr_count($line, ';') > substr_count($line, ',')) {
                return ';';
            }
        }
        return ',';
    }

    /**
     * Helper : Nettoie les en-têtes CSV (BOM, espaces, majuscules).
     */
    private function cleanHeaders(array $headers): array {
        $bom = pack('H*','EFBBBF');
        $headers[0] = preg_replace("/^$bom/", '', $headers[0]);
        
        return array_map(function($h) {
            return strtolower(trim($h));
        }, $headers);
    }

    /**
     * Récupère la liste des villes (utile pour les filtres).
     */
    public function getAvailableCities(): array{
        if (!file_exists($this->csvPath)) return [];
        $cities = [];
        
        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            
            if ($rawHeaders) {
                $headers = $this->cleanHeaders($rawHeaders);
                $cityIndex = array_search('ville', $headers);

                if ($cityIndex !== false) {
                    while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                        if (isset($row[$cityIndex])) {
                            $cities[] = trim($row[$cityIndex]);
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
     */
    public function getEnergyData(string $type, string $city, string $from, string $to, ?string $compareCity = null): array{
        if (!file_exists($this->csvPath)) return $this->fmt($type, $city, $from, $to, []);

        $results = [];
        
        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            
            if ($rawHeaders) {
                $headers = $this->cleanHeaders($rawHeaders);

                while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                    if (count($row) !== count($headers)) continue;
                    $data = array_combine($headers, $row);

                    if ($type !== 'all' && (!isset($data['type']) || strtolower(trim($data['type'])) !== strtolower($type))) {
                        continue;
                    }

                    $rowCity = strtolower(trim($data['ville'] ?? ''));
                    $targetCity = strtolower($city);
                    $compCity = $compareCity ? strtolower($compareCity) : null;

                    if ($city !== 'all') {
                        if ($rowCity !== $targetCity && $rowCity !== $compCity) {
                            continue;
                        }
                    }

                    // 3. Date
                    if (isset($data['date_heure'])) {
                        $cleanDate = str_replace('/', '-', $data['date_heure']);
                        $d = date('Y-m-d', strtotime($cleanDate));
                        
                        if ($d >= $from && $d <= $to) {
                            $results[] = [
                                'date' => $data['date_heure'],
                                'production' => (float)($data['production_kw'] ?? 0),
                                'ville' => $data['ville'],
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
            return strtotime(str_replace('/', '-', $a['date'])) - strtotime(str_replace('/', '-', $b['date']));
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
            $headers = $this->cleanHeaders(fgetcsv($handle, 1000, $this->delimiter, "\"", "\\"));
            
            while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                if (count($row) !== count($headers)) continue;
                $data = array_combine($headers, $row);

                if (strtolower($data['type'] ?? '') === strtolower($type) && 
                    strtolower($data['ville'] ?? '') === strtolower($city) &&
                    (float)($data['valeur_meteo'] ?? 0) > 10) { 
                    
                    $prod = (float)($data['production_kw'] ?? 0);
                    $meteo = (float)($data['valeur_meteo'] ?? 0);
                    
                    if ($meteo > 0) {
                        $totalRatio += ($prod / $meteo);
                        $count++;
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
        $hourly = $apiData['hourly'] ?? [];
        $predictions = [];

        if (isset($hourly['time'])) {
            foreach ($hourly['time'] as $index => $isoDate) {
                $dateString = str_replace('T', ' ', $isoDate) . ':00';
                $dayOnly = substr($dateString, 0, 10); 
                
                if ($dayOnly < $startDate || $dayOnly > $endDate) continue;

                $temp = $hourly['temperature_2m'][$index] ?? 0;
                $rain = $hourly['precipitation'][$index] ?? 0;
                $wind = $hourly['wind_speed_10m'][$index] ?? 0;
                $sun  = $hourly['shortwave_radiation'][$index] ?? 0;

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
     */
    private function fmt($type, $city, $from, $to, $data): array {
        return ['type' => $type, 'city' => $city, 'from' => $from, 'to' => $to, 'data' => $data];
    }
}