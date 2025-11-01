<?php
/**
 * Fichier : EnergyModel.php
 * Rôle : 
 * Auteur : Lucas LEPAPE, Adam Bougherara
 */

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class EnergyModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    /**
     * Récupère les données énergétiques d'une ville entre deux dates.
     */
    public function getEnergyData(string $city, string $type, string $from, string $to): array
    {
        $validTypes = ['solar', 'wind', 'hydro'];
        if (!in_array(strtolower($type), $validTypes, true)) {
            throw new Exception("Type de production invalide : $type");
        }

        $query = "
            SELECT city, date, type, production, consumption
            FROM energy_data
            WHERE city = :city
              AND type = :type
              AND date BETWEEN :from AND :to
            ORDER BY date ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':city', strtolower($city));
        $stmt->bindValue(':type', strtolower($type));
        $stmt->bindValue(':from', $from);
        $stmt->bindValue(':to', $to);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            throw new Exception("Aucune donnée trouvée pour $city entre $from et $to ($type)");
        }

        return [
            'asset' => ucfirst($city),
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'data' => $results
        ];
    }
}