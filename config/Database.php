<?php
/**
 * Fichier : Database.php
 * Rôle : Fournit une connexion unique à la BDD avec PDO.
 * Auteur : Lucas LEPAPE
 * Date : 2025
 * 
 * Cette classe permet d'établir une connexion avec la BDD.
 */

use PDO;
use PDOException;

/**
 * Classe Database
 * 
 * Gère la connexion avec la BDD avec PDO pour obtenir un object PDO
 */
class Database {

    /**
     * @var $pdo Instance unique connexion PDO
     * @var $db lien de connexion avec la BDD
     * @var $dbuser nom d'utiliseur de la BDD
     * @var $dbpass mot de passe de la BDD
     */
    private static $pdo = null;
    private $host = getenv('DATABASE_HOST');
    private $dbname = getenv('DATABASE_NAME');
    private $dbuser = getenv('DATABASE_USER');
    private $dbpass = getenv('DATABASE_PASSWORD');

    /**
     * Retourne la connexion PDO à la BDD
     * 
     * Si la connexion n'existe pas, la crée.
     * Sinon renvoie simplement celle déjà crée.
     * 
     * @return PDO Object de la connexion à la BDD
     */
    public static function getConnection(): PDO {
        if (self::$pdo === null) {
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