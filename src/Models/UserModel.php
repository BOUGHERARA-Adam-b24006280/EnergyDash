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

    protected string $table = 'users';

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
            'role'       => 'user'
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
        try {
            $stmt = $this->db->prepare(
                "SELECT id, first_name, last_name, email, password, role 
                 FROM users 
                 WHERE email = :email LIMIT 1"
            );
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
            return null;
        }
    }

    public function updateUser(int $id, string $firstName, string $lastName, string $email, string $password = ''): bool
    {
        $query = "UPDATE users SET first_name = :first, last_name = :last, email = :email";
        $params = [
            ':first' => $firstName,
            ':last'  => $lastName,
            ':email' => $email,
            ':id'    => $id
        ];

        if (!empty($password)) {
            $query .= ", password = :password";
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $query .= " WHERE id = :id";

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Met à jour le rôle d’un utilisateur
     * 
     * @param int $id Identifiant de l'utilisateur
     * @param string $role Nouveau rôle ('admin' ou 'user')
     * @return bool
     */
    public function updateUserRole(int $id, string $role): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
            return $stmt->execute([':role' => $role, ':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour rôle : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les utilisateurs depuis la base de données
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllUsers(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, first_name, last_name, email, role FROM users ORDER BY last_name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de tous les utilisateurs : " . $e->getMessage());
            return [];
        }
    }
}
