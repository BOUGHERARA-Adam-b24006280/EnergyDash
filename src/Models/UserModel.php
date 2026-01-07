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
class UserModel extends Model {
    protected string $table = 'users';

    public function __construct(){
        $db = (new Database())->getConnection();
        parent::__construct($db);
    }

    /**
     * Vérifie si une adresse mail est déjà utilisée
     */
    public function emailExists(string $email): bool {
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
    public function createUser(string $firstName, string $lastName, string $email, string $password): bool {
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
    public function getUserByEmail(string $email): ?array {
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

    public function updateUser(int $id, string $firstName, string $lastName, string $email, string $password = ''): bool {
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

    public function updateUserRole(int $id, string $role): bool {
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
    public function getAllUsers(): array {
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

    public function storeResetToken(int $userId, string $hashedToken, string $expiresAt): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (user_id, token, created_at, expires_at)
                VALUES (:user_id, :token, :created_at, :expires_at)
            ");
            return $stmt->execute([
                ':user_id'   => $userId,
                ':token'     => $hashedToken,
                ':created_at'=> date('Y-m-d H:i:s'),
                ':expires_at'=> $expiresAt
            ]);
        } catch (PDOException $e) {
            error_log('Erreur storeResetToken : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un utilisateur associé à un token de réinitialisation.
     *
     * @param string $token Token haché (SHA-256)
     * @return array<string, mixed>|null Retourne les infos de l'utilisateur ou null si invalide/expiré
     */
    public function getUserByToken(string $token): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.first_name, u.last_name, u.email
                FROM users u
                JOIN password_resets pr ON pr.user_id = u.id
                WHERE pr.token = :token AND pr.expires_at > :now
                LIMIT 1
            ");
            $stmt->execute([
                ':token' => $token,
                ':now'   => date('Y-m-d H:i:s')
            ]);

            /** @var array<string, mixed>|false $result */
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log('Erreur getUserByToken : ' . $e->getMessage());
            return null;
        }
    }

    public function updatePassword(int $userId, string $newPassword): bool{
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = :pass WHERE id = :id");
            return $stmt->execute([':pass' => $hashed, ':id' => $userId]);
        } catch (PDOException $e) {
            error_log('Erreur updatePassword : ' . $e->getMessage());
            return false;
        }
    }

    public function invalidateToken(string $token): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = :token");
            return $stmt->execute([':token' => $token]);
        } catch (PDOException $e) {
            error_log('Erreur invalidateToken : ' . $e->getMessage());
            return false;
        }
    }

    public function deleteResetTokensForUser(int $userId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = :user_id");
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('Erreur deleteResetTokensForUser : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie les identifiants et retourne l'user si OK, sinon null
     */
    public function verifyLogin(string $email, string $password): ?array {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    /**
     * Logique centralisée de validation de mot de passe
     */
    public static function isPasswordStrong(string $password): bool {
        return mb_strlen($password) >= 8 
            && preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
    }

    /**
     * Génère un token de réinitialisation pour l'email donné.
     * @return array{token: string, user: array<string, mixed>}|null Retourne le token (clair) et l'user, ou null si introuvable.
     */
    public function createResetTokenForEmail(string $email): ?array
    {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $this->deleteResetTokensForUser($user['id']);
        $this->storeResetToken($user['id'], $hashedToken, $expiresAt);

        return ['token' => $token, 'user' => $user];
    }
}