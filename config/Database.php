<?php
/**
 * Fichier : Database.php
 * Rôle : Fournit une connexion unique à la BDD avec PDO.
 * Auteur : Lucas LEPAPE
 */

use PDO;
use PDOException;

/**
 * Classe Database qui gère la connexion avec la BDD
 */
class Database {
    private static ?PDO $pdo = null;

    /**
     * @return PDO Object de la connexion à la BDD
     */
    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            $host = getenv('DATABASE_HOST') ?? 'localhost';
            $dbname = getenv('DATABASE_NAME') ?? 'energy_dash';
            $username = getenv('DATABASE_USER') ?? 'root';
            $password = getenv('DATABASE_PASSWORD') ?? '';

            try {
                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Erreur de connexion à la BDD :' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}