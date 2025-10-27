<?php
/**
 * Fichier : UserModel.php
 * Rôle : Gère les interactions avec la table 'users'
 * Auteur : Lucas LEPAPE
 */

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

/**
 * Classe UserModel qui gère les intéractions avec la table 'users'
 */
class UserModel {
    /** @var PDO Connexion à la base de données */
    private PDO $db;

    /**
     * Constructeur : récupère la connexion PDO depuis la classe Database
     */
    public function __construct() {
        $this->db = (new DataBase())->getConnection();
    }

    /**
     * Vérifie si une adresse mail est déjà utilisée
     * 
     * @param string $email Email de l'utilisateur
     * @return bool true si l'email existe déjà
     * @throws PDOException En cas d'erreur
     */
    public function emailExists(string $email): bool {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        }catch(PDOException $e) {
            error_log("Erreur lors de la vérification d'email : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enregistre un nouvelle utilisateur
     * 
     * @param string $firstName Prénom
     * @param string $lastName Nom
     * @param string $email Adresse mail
     * @param string $password Mot de passe
     * @return bool true si la création fonctionne
     * @throws PDOException En cas d'erreur
     */
    public function createUser(string $firstName, string $lastName, string $email, string $password) : bool {
        try{
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                INSERT INTO users (first_name, last_name, email, password)
                VALUES (:first_name, :last_name, :email, :password)
            ");
            
            $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la création d'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un utilisateur par son email
     * 
     * @param string $email
     * @return array{id:int, first_name:string, last_name:string, email:string, password:string}|null
     */
    public function getUserByEmail(string $email): ?array {
        try {
            $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            /** @var array{id: int, first_name: string, last_name: string, email: string, password: string}|false $user */
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user === false) {
                return null;
            }

            return ['id' => (int) $user['id'], 'first_name' => (string) $user['first_name'], 'last_name' => (string) $user['last_name'], 'email' => (string) $user['email'], 'password' => (string) $user['password'], ];
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération d'utilisateur : " . $e->getMessage());
            return null;
        }
    }
}