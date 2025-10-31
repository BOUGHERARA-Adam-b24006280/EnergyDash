<?php
/**
 * Fichier : UserModel.php
 * Rôle : Gère les interactions avec la table 'users'
 * Auteur : Mohamed-Amine Haddad, Lucas LEPAPE
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;
use PDOException;

/**
 * Classe UserModel qui gère les intéractions avec la table 'users'
 */
class UserModel extends Model{
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
     * Crée un nouvel utilisateur.
     *
     * @param string $firstName Prénom
     * @param string $lastName Nom
     * @param string $email Adresse mail
     * @param string $password Mot de passe
     * @return bool True si l’insertion réussit, false sinon
     */
    public function createUser(string $firstName, string $lastName, string $email, string $password): bool
    {
        // Hasher le mot de passe avant insertion
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Appel de la méthode générique create() du Model parent
        return $this->create([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'password'   => $hashedPassword,
        ]) !== false;
    }

    /**
     * Récupère un utilisateur par son adresse e-mail.
     *
     * @param string $email
     * @return array<string, mixed>|null
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->findOneBy('email', $email);
    }
}