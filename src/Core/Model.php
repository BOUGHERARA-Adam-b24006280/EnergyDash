<?php
namespace App\Core;

use PDO;

abstract class Model
{
    /** @var PDO Instance de connexion à la base de données via PDO */
    protected PDO $db;
    
    /** @var string Nom de la table associée au modèle */
    protected string $table;

    /**
     * Constructeur.
     *
     * @param PDO $db Instance de connexion PDO.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère toutes les lignes de la table.
     *
     * @return array<int, array<string, mixed>> Liste des lignes sous forme de tableaux associatifs.
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);

        if ($stmt === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une ligne par son identifiant.
     *
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Recherche une ligne par une colonne spécifique.
     *
     * @param string $column
     * @param mixed $value
     * @return array<string, mixed>|null
     */
    public function findOneBy(string $column, mixed $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['value' => $value]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Crée une nouvelle ligne dans la table et renvoie son ID.
     *
     * @param array<string, mixed> $data
     * @return int|false ID inséré ou false en cas d’échec
     */
    public function create(array $data): int|false
    {
        if (empty($data)) {
            return false;
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);

        try {
            $success = $stmt->execute($data);
            if ($success) {
                return (int) $this->db->lastInsertId();
            }
            return false;
        } catch (\Throwable $e) {
            error_log('DB insert error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour une ligne par ID.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $set WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;

        try {
            $stmt->execute($data);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('DB update error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('DB delete error: ' . $e->getMessage());
            return false;
        }
    }
}