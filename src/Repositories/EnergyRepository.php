<?php
/**
 * Fichier : EnergyRepository.php
 * Rôle : Responsable de l'extraction et du filtrage des données énergétiques.
 */

namespace App\Repositories;

use App\Infrastructure\CsvReader;

/**
 * * Cette classe agit comme une couche d'accès aux données.
 */
class EnergyRepository {

    /** @var CsvReader Instance du lecteur technique de fichiers CSV. */
    private CsvReader $reader;

    /**
     * Constructeur de la classe.
     * @param CsvReader $reader Le lecteur CSV à utiliser pour l'extraction des données.
     */
    public function __construct(CsvReader $reader) {
        $this->reader = $reader;
    }

    /**
     * Récupère la liste unique et triée de toutes les villes présentes dans les données.
     * @return array<int, string> Liste des noms de villes.
     */
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

    /**
     * Génère une cartographie des types d'énergie disponibles par ville.
     * @return array<string, array<int, string>> Tableau associatif [Ville => [Type1, Type2...]].
     */
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

    /**
     * Extrait les données de production énergétique en fonction de filtres spécifiques.
     * @param string $type Le type d'énergie ('solaire', 'eolien', etc. ou 'all').
     * @param string $city Le nom de la ville cible (ou 'all').
     * @param string $from Date de début (Y-m-d).
     * @param string $to Date de fin (Y-m-d).
     * @param string|null $compareCity Optionnel : nom d'une ville secondaire pour comparaison.
     * @return array<int, array{date: string, production: float, ville: string, meteo: float, temp: float, statut: string}> Liste des relevés formatés.
     */
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
     * Récupère les données historiques brutes pour le calcul des ratios de performance.
     * @param string $type Le type d'énergie.
     * @param string $city La ville concernée.
     * @return array<int, array{production: float, meteo: float}> Liste des paires production/météo.
     */
    public function getHistoricalDataForRatio(string $type, string $city): array {
        $results = [];
        foreach ($this->reader->getRows() as $data) {
            $dataType = strtolower((string)($data['type'] ?? ''));
            $dataVille = strtolower((string)($data['ville'] ?? ''));
            $dataMeteo = (float)($data['valeur_meteo'] ?? 0);

            if ($dataType === strtolower($type) && $dataVille === strtolower($city) && $dataMeteo > 0.1) { 
                $results[] = [
                    'production' => (float)($data['production_kw'] ?? 0),
                    'meteo' => $dataMeteo
                ];
            }
        }
        return $results;
    }
}