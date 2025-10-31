<?php
/**
 * Fichier : Database.php
 * Rôle : Fournit une connexion unique à la BDD avec PDO
 * Auteur : Lucas LEPAPE
 */

namespace App\Core;

use PDO;
use PDOException;

/**
 * Classe Database qui gère la connexion avec la BDD
 */
class Database {
    /** @var PDO|null Instance de la connexion PDO, ou null si non init */
    private ?PDO $pdo = null;
    /** @var string Nom d'hôte du serveur de la BDD */
    private string $host;
    /** @var string Nom de la BDD */
    private string $dbname;
    /** @var string Nom d'utilisateur pour la connexion */
    private string $username;
    /** @var string Mot de passe associé à l'utilisateur */
    private string $password;

    /**
     * Constructeur qui initialise les informations de connexion à partir des variables d'environnement
     */
    public function __construct()
    {
        // Récupération sécurisée des variables d'environnement
        $host = $_ENV['DATABASE_HOST'] ?? NULL;
        $dbname = $_ENV['DATABASE_NAME'] ?? NULL;
        $username = $_ENV['DATABASE_USER'] ?? NULL;
        $password = $_ENV['DATABASE_PASSWORD'] ?? NULL;

        // Vérification que tout est bien une chaîne
        if (!is_string($host) || !is_string($dbname) || !is_string($username) || !is_string($password)) {
            throw new \RuntimeException('Les variables d’environnement de la base de données sont invalides ou manquantes.');
        }

        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
    }


    /**
     * @return PDO Objet de la connexion à la BDD
     * @throws PDOException En cas d'erreur de connexion
     */
    public function getConnection(): PDO {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
                $this->pdo = new PDO($dsn, $this->username, $this->password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new \RuntimeException('Erreur de connexion à la base de données.', 0, $e);
            }

        }
        return $this->pdo;
    }
}