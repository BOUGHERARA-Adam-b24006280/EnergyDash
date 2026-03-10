<?php
namespace App\Repositories;

use App\Infrastructure\CsvReader;

/**
 * Dépôt (Repository) responsable de l'extraction des données énergétiques ciblées.
 */
class EnergyRepository {
    private CsvReader $reader;

    public function __construct(CsvReader $reader) {
        $this->reader = $reader;
    }

    public function getAvailableCities(): array {
        $cities = [];
        foreach ($this->reader->getRows() as $row) {
            if (isset($row['ville'])) {
                $cities[] = trim((string)$row['ville']);
            }
        }
        $unique = array_unique($cities);
        sort($unique);
        return $unique;
    }

    public function getCityEnergyMapping(): array {
        $mapping = [];
        foreach ($this->reader->getRows() as $row) {
            if (isset($row['ville']) && isset($row['type'])) {
                $city = trim((string)$row['ville']);
                $type = strtolower(trim((string)$row['type']));
                
                if (!isset($mapping[$city])) $mapping[$city] = [];
                if (!in_array($type, $mapping[$city])) $mapping[$city][] = $type;
            }
        }
        ksort($mapping);
        return $mapping;
    }

    public function getEnergyData(string $type, string $city, string $from, string $to, ?string $compareCity = null): array {
        $results = [];
        foreach ($this->reader->getRows() as $data) {
            $dataType = isset($data['type']) ? strtolower(trim((string)$data['type'])) : '';
            $dataVille = isset($data['ville']) ? strtolower(trim((string)$data['ville'])) : '';

            if ($type !== 'all' && $dataType !== strtolower($type)) continue;

            $targetCity = strtolower($city);
            $compCity = $compareCity ? strtolower($compareCity) : null;

            if ($city !== 'all') {
                if ($dataVille !== $targetCity && $dataVille !== $compCity) continue;
            }

            if (isset($data['date_heure'])) {
                $ts = strtotime(str_replace('/', '-', (string)$data['date_heure']));
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
        
        usort($results, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $results;
    }

    /**
     * Méthode interne pour récupérer les données brutes nécessaires au calcul du ratio.
     */
    public function getHistoricalDataForRatio(string $type, string $city): array {
        $results = [];
        foreach ($this->reader->getRows() as $data) {
            $dataType = strtolower((string)($data['type'] ?? ''));
            $dataVille = strtolower((string)($data['ville'] ?? ''));
            $dataMeteo = (float)($data['valeur_meteo'] ?? 0);

            if ($dataType === strtolower($type) && $dataVille === strtolower($city) && $dataMeteo > 10) { 
                $results[] = [
                    'production' => (float)($data['production_kw'] ?? 0),
                    'meteo' => $dataMeteo
                ];
            }
        }
        return $results;
    }
}