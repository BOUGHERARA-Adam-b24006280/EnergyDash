<?php
namespace App\Models;

class EnergyModel
{
    private string $csvPath;
    private string $delimiter = ','; // Par défaut

    public function __construct()
    {
        // --- CORRECTION : ON FORCE LE DÉMARRAGE DE SESSION ---
        // Sans ça, l'API ne sait pas qui tu es et charge le fichier par défaut.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user']['id'] ?? null;
        $userRole = $_SESSION['user']['role'] ?? 'user';

        // Chemins des fichiers
        $userFile = __DIR__ . '/../../Storage/energy_user_' . $userId . '.csv';
        $defaultFile = __DIR__ . '/../../Storage/energyData.csv';

        $canUpload = in_array($userRole, ['admin', 'editor']);

        if ($userId && $canUpload && file_exists($userFile)) {
            $this->csvPath = $userFile;
        } else {
            $this->csvPath = $defaultFile;
        }
        
        // Détection séparateur (Code existant...)
        if (file_exists($this->csvPath)) {
            $this->delimiter = $this->detectDelimiter($this->csvPath);
        }
    }

    /**
     * Regarde la première ligne pour voir si c'est du format Excel (;) ou Standard (,)
     */
    private function detectDelimiter(string $file): string
    {
        $handle = fopen($file, "r");
        if ($handle) {
            $line = fgets($handle); // Lit la première ligne brute
            fclose($handle);
            // Si on trouve plus de points-virgules que de virgules, c'est du Excel FR
            if (substr_count($line, ';') > substr_count($line, ',')) {
                return ';';
            }
        }
        return ',';
    }

    private function cleanHeaders(array $headers): array
    {
        // Nettoie le BOM (caractère invisible Excel)
        $bom = pack('H*','EFBBBF');
        $headers[0] = preg_replace("/^$bom/", '', $headers[0]);
        
        return array_map(function($h) {
            return strtolower(trim($h));
        }, $headers);
    }

    public function getAvailableCities(): array
    {
        if (!file_exists($this->csvPath)) return [];
        $cities = [];
        
        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            // Utilise le délimiteur détecté ($this->delimiter)
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

    // On ajoute le paramètre $compareCity
    public function getEnergyData(string $type, string $city, string $from, string $to, ?string $compareCity = null): array
    {
        if (!file_exists($this->csvPath)) return $this->fmt($type, $city, $from, $to, []);

        $results = [];
        
        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $rawHeaders = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\");
            
            if ($rawHeaders) {
                $headers = $this->cleanHeaders($rawHeaders);

                while (($row = fgetcsv($handle, 1000, $this->delimiter, "\"", "\\")) !== FALSE) {
                    if (count($row) !== count($headers)) continue;
                    $data = array_combine($headers, $row);

                    // --- FILTRES ---
                    // 1. Type
                    if ($type !== 'all' && (!isset($data['type']) || strtolower(trim($data['type'])) !== strtolower($type))) {
                        continue;
                    }

                    // 2. Ville (MODIFIÉ POUR LA COMPARAISON)
                    $rowCity = strtolower(trim($data['ville'] ?? ''));
                    $targetCity = strtolower($city);
                    $compCity = $compareCity ? strtolower($compareCity) : null;

                    // Si on ne veut pas "toutes les zones"
                    if ($city !== 'all') {
                        // On garde la ligne SI c'est la ville 1 OU la ville 2
                        // Si ce n'est ni l'une ni l'autre, on passe à la suivante
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
        // On trie les résultats par date pour avoir un graphique propre
        usort($results, function($a, $b) {
            return strtotime(str_replace('/', '-', $a['date'])) - strtotime(str_replace('/', '-', $b['date']));
        });

        return $this->fmt($type, $city, $from, $to, $results);
    }

    /**
     * Calcule la compétence de l'installation (Ratio Historique).
     * Retourne combien de kW sont produits pour 1 unité de météo.
     */
    private function getPerformanceRatio(string $type, string $city): float {
        if (!file_exists($this->csvPath)) return 0;

        $totalRatio = 0;
        $count = 0;

        if (($handle = fopen($this->csvPath, "r")) !== FALSE) {
            $headers = $this->cleanHeaders(fgetcsv($handle, 1000, $this->delimiter, '"', "\\"));
            
            while (($row = fgetcsv($handle, 1000, $this->delimiter, '"', "\\")) !== FALSE) {
                if (count($row) !== count($headers)) continue;
                $data = array_combine($headers, $row);

                // On filtre : Bonne ville, bon type, et météo suffisante (> 10)
                if (strtolower($data['type']) === strtolower($type) && 
                    strtolower($data['ville']) === strtolower($city) &&
                    (float)($data['valeur_meteo'] ?? 0) > 10) { 
                    
                    $prod = (float)$data['production_kw'];
                    $meteo = (float)$data['valeur_meteo'];
                    
                    // Ratio = Production / Météo
                    if ($meteo > 0) {
                        $totalRatio += ($prod / $meteo);
                        $count++;
                    }
                }
            }
            fclose($handle);
        }

        // Si on a des données, on fait la moyenne, sinon on renvoie 0
        return ($count > 0) ? $totalRatio / $count : 0;
    }

    /**
     * SIMULATEUR HYBRIDE (Passé et Futur)
     * Utilise la météo réelle (Archive) ou prévue (Forecast) pour générer des données.
     */
    public function simulateDataFromWeather(string $type, string $city, string $startDate, string $endDate): array {
        
        // 1. Coordonnées GPS
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

        // 2. Ratio de performance
        $performanceRatio = $this->getPerformanceRatio($type, $city);
        if ($performanceRatio <= 0) {
            if ($type === 'solaire') $performanceRatio = 0.005; 
            elseif ($type === 'eolien') $performanceRatio = 0.1; 
            else $performanceRatio = 0.5;
        }

        // 3. CHOIX DE L'API
        $today = date('Y-m-d');
        $apiUrl = "";

        // Si la date demandée est dans le passé, on utilise l'ARCHIVE
        if ($startDate < $today) {
            $apiUrl = "https://archive-api.open-meteo.com/v1/archive?latitude={$coords['lat']}&longitude={$coords['lon']}&start_date={$startDate}&end_date={$endDate}&hourly=temperature_2m,precipitation,wind_speed_10m,shortwave_radiation&timezone=Europe%2FParis";
        } else {
            // Sinon on utilise la PRÉVISION (Forecast)
            $apiUrl = "https://api.open-meteo.com/v1/forecast?latitude={$coords['lat']}&longitude={$coords['lon']}&hourly=temperature_2m,precipitation,wind_speed_10m,shortwave_radiation&timezone=Europe%2FParis";
        }

        // 4. RÉCUPÉRATION AVEC PATCH SSL (C'est ICI que ça coinçait)
        // On crée un contexte pour dire à PHP : "T'inquiète pas pour le certificat HTTPS"
        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
            "http" => [
                "timeout" => 5 // On attend max 5 secondes
            ]
        ]);

        // On utilise ce contexte pour télécharger le JSON
        $json = file_get_contents($apiUrl, false, $context);

        if ($json === false) {
            return $this->fmt($type, $city, $startDate, $endDate, []); // Retourne vide si échec
        }

        $apiData = json_decode($json, true);
        $hourly = $apiData['hourly'] ?? [];
        
        $predictions = [];

        if (isset($hourly['time'])) {
            foreach ($hourly['time'] as $index => $isoDate) {
                // Conversion date
                $dateString = str_replace('T', ' ', $isoDate) . ':00';
                
                // Filtrage strict des dates demandées
                $dayOnly = substr($dateString, 0, 10); 
                if ($dayOnly < $startDate || $dayOnly > $endDate) continue;

                // Données météo
                $temp = $hourly['temperature_2m'][$index];
                $rain = $hourly['precipitation'][$index];
                $wind = $hourly['wind_speed_10m'][$index];
                $sun  = $hourly['shortwave_radiation'][$index];

                $predictedProd = 0;
                $meteoValueForChart = 0;

                // --- CALCUL ---
                if ($type === 'solaire') {
                    $predictedProd = $sun * $performanceRatio;
                    if ($temp > 25) $predictedProd *= 0.95;
                    $meteoValueForChart = $sun;
                }
                elseif ($type === 'eolien') {
                    if ($wind > 10) $predictedProd = $wind * $performanceRatio;
                    $meteoValueForChart = $wind;
                }
                elseif ($type === 'hydraulique') {
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

    private function fmt($type, $city, $from, $to, $data): array {
        return ['type' => $type, 'city' => $city, 'from' => $from, 'to' => $to, 'data' => $data];
    }
}