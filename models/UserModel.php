<?php
/**
 * Fichier : UserModel.php
 * Rôle : Contient les fonctions liées aux utilisateurs.
 * Auteur : Lucas LEPAPE
 */

require_once __DIR__ . '/../config/Database.php';

class UserModel {
    /** @var PDO Connexion à la BDD */
    private PDO $pdo;

    public function __construct() {
        $this->pdo = (new Database()) -> getConnection();
    }

    /**
     * Enregiste un nouvel utilisateur dans la BDD
     * 
     * @param string $email
     * @param string $password
     * @return bull Retourne true si réussit, false sinon.
     */
    public function register(string $email, string $password): bool {
        if ($this->getByEmail($email) !== null){
            return false;
        }
    }

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (email, password) 
            VALUES ($email, $password)
        ");
    } catch (PDOException $e) {
        error_log("Erreur lors de l'inscription : " . $e->getMessage());
        return false;
    }
}