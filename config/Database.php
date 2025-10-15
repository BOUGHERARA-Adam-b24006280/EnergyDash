<?php
/**
 * Fichier : Database.php
 * Rôle : Fournit une connexion unique à la BDD avec PDO.
 * Auteur : Lucas LEPAPE
 */

use PDO;
use PDOException;

/**
 * Classe Database qui gère la connexion avec la BDD.
 */
class Database {
    /** @var PDO|null Instance de la connexion PDO, ou null si non init */
    private ?PDO $pdo = null;
    /** @var string  Nom d'hôte du serveur de la BDD */
    private string $host;
    /** @var string Nom de la BDD */
    private string $dbname;
    /** @var string Nom d'utilisateur pour la connexion */
    private string $username;
    /** @var string Mot de passe associé à l'utilisateur */
    private string $password;

    /**
     * Constructeur qui initialise les informations de connexion à partir des variables d'environnement.
     */
    public function __construct() {
        $this->host = getenv('DATABASE_HOST') ?? 'localhost';
        $this->dbname = getenv('DATABASE_NAME') ?? 'energy_dash';
        $this->username = getenv('DATABASE_USER') ?? 'root';
        $this->password = getenv('DATABASE_PASSWORD') ?? '';
    }

    /**
     * @return PDO Object de la connexion à la BDD.
     * @throws PDOException En cas d'erreur de connexion.
     */
    public function getConnection(): PDO {
        if ($this->pdo === null) {
            try {
                $this->pdo = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8mb4", $this->username, $this->password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                error_log('Erreur de connexion à la BDD :' . $e->getMessage());
                die('Une erreur interne est survenue. Veuillez réessayer plus tard.');
            }
        }
        return $this->pdo;
    }
}