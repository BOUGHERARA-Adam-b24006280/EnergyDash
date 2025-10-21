<?php
namespace App\Core;

use PDO;
use App\Core\Database;

abstract class Model
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
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
     * Crée une nouvelle ligne dans la table.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): bool
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
            return $stmt->execute($data);
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