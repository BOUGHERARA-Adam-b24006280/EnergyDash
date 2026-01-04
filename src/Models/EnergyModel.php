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

    private function fmt($type, $city, $from, $to, $data): array {
        return ['type' => $type, 'city' => $city, 'from' => $from, 'to' => $to, 'data' => $data];
    }
}