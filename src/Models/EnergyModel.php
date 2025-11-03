<?php
/**
 * Fichier : EnergyModel.php
 * Rôle : Gère les interactions avec la table 'energy_data'
 * Auteur : Lucas LEPAPE, Adam Bougherara
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;
use Exception;

class EnergyModel extends Model
{
    // 🔹 Nom de la table associée à ce modèle
    protected string $table = 'energy_data';

    /**
     * Constructeur : initialise la connexion PDO via Database
     */
    public function __construct()
    {
        $db = (new Database())->getConnection(); // se connecte à la BDD à partir du .env
        parent::__construct($db);                // passe la connexion au Model parent
    }

    /**
     * Récupère les données énergétiques d'une ville entre deux dates.
     *
     * @param string $city Nom de la ville
     * @param string $type Type d’énergie (solar, wind, hydro)
     * @param string $from Date de début (YYYY-MM-DD)
     * @param string $to   Date de fin (YYYY-MM-DD)
     * @return array<string, mixed>
     * @throws Exception Si le type est invalide ou aucune donnée trouvée
     */
    public function getEnergyData(string $city, string $type, string $from, string $to): array
    {
        $validTypes = ['solar', 'wind', 'hydro'];
        if (!in_array(strtolower($type), $validTypes, true)) {
            throw new Exception("Type de production invalide : $type");
        }

        $sql = "
            SELECT city, date, type, production, consumption
            FROM {$this->table}
            WHERE city = :city
              AND type = :type
              AND date BETWEEN :from AND :to
            ORDER BY date ASC
        ";

        $stmt = $this->db->prepare($sql);
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
            'type'  => $type,
            'from'  => $from,
            'to'    => $to,
            'data'  => $results,
        ];
    }
}