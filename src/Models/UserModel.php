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
 * Classe UserModel qui gère les interactions avec la table 'users'
 */
class UserModel extends Model
{
    protected string $table = 'users';

    public function __construct()
    {
        $db = (new Database())->getConnection();
        parent::__construct($db);
    }

    /**
     * Vérifie si une adresse mail est déjà utilisée
     */
    public function emailExists(string $email): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification d'email : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée un nouvel utilisateur.
     */
    public function createUser(string $firstName, string $lastName, string $email, string $password): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
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
            $stmt = $this->db->prepare("
                SELECT id, first_name, last_name, email, password, role 
                FROM users 
                WHERE email = :email LIMIT 1
            ");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            /** @var array<string, mixed>|false $result */
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result !== false ? $result : null;
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

        if ($password !== '') {
            $query .= ", password = :password";
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $query .= " WHERE id = :id";

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

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
     * Récupère tous les utilisateurs.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllUsers(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, first_name, last_name, email, role 
                FROM users 
                ORDER BY last_name ASC
            ");

            if ($stmt === false) {
                return [];
            }

            /** @var array<int, array<string, mixed>> $results */
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de tous les utilisateurs : " . $e->getMessage());
            return [];
        }
    }
}
