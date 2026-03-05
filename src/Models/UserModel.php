<?php
/**
 * Fichier : UserModel.php
 * Rôle : Gère les interactions avec la table 'users'
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

    /**
     * Constructeur.
     * Initialise la connexion à la base de données.
     */
    public function __construct(){
        $db = Database::getInstance();
        parent::__construct($db);
    }

    /**
     * Vérifie si une adresse mail est déjà utilisée
     * 
     * @param string $email L'adresse email à vérifier.
     * @return bool true si l'email existe déjà, false sinon.
     */
    public function emailExists(string $email): bool {
        return $this->findOneBy('email', $email) !== null;
    }

    /**
     * Crée un nouvel utilisateur.
     * 
     * @param string $firstName Prénom de l'utilisateur.
     * @param string $lastName Nom de l'utilisateur.
     * @param string $email Adresse email de l'utilisateur.
     * @param string $password Mot de passe (haché avant stockage).
     * @return bool true en cas de succès, false sinon.
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
     * @param string $email L'adresse email de l'utilisateur.
     * @return array<string, mixed>|null Les données de l'utilisateur ou null si introuvable.
     */
    public function getUserByEmail(string $email): ?array {
        return $this->findOneBy('email', $email);
    }

    /**
     * Met à jour les informations d'un utilisateur.
     * 
     * @param int $id Identifiant de l'utilisateur.
     * @param string $firstName nouveau prénom.
     * @param string $lastName Nouveau nom.
     * @param string $email Nouvel email.
     * @param string $password (Optionnel) Nouveau mot de passe.
     * @return bool true en cas de succès. 
     */
    public function updateUser(int $id, string $firstName, string $lastName, string $email, string $password = ''): bool {
        $data = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
        ];

        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $this->update($id, $data);
    }

    /**
     * Modifie le rôle d'un utilisateur.
     * 
     * @param int $id Identifiant de l'utilisateur.
     * @param string $role Nouveau rôle ('user', 'admin', 'editor').
     * @return bool true en cas de succès, false si le rôle est invalide ou erreur SQL.
     */
    public function updateUserRole(int $id, string $role): bool {
        $allowedRoles = ['user', 'admin', 'editor'];

        if (!in_array($role, $allowedRoles, true)) {
            return false;
        }

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
     * @return array<int, array<string, mixed>> La liste des utliisateurs.
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

    /**
     * Stocke un token de réintialisation de mot de passe en base.
     * 
     * @param int $userId Identifiant de l'utilisateur.
     * @param string $hashedToken Le token haché.
     * @param string $expiresAt Date d'expiration.
     * @return bool true en cas de succès.
     */
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
     * @param string $token Token haché (SHA-256).
     * @return array<string, mixed>|null Retourne les infos de l'utilisateur ou null si invalide/expiré.
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

    /**
     * Met à jour le mot de passe d'un utilisateur.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @param string $newPassword Nouveau mot de passe.
     * @return bool true en cas de succès.
     */
    public function updatePassword(int $userId, string $newPassword): bool {
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = :pass WHERE id = :id");
            return $stmt->execute([':pass' => $hashed, ':id' => $userId]);
        } catch (PDOException $e) {
            error_log('Erreur updatePassword : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalide (supprime) un token de réinitialisation.
     *
     * @param string $token Le token à supprimer.
     * @return bool true en cas de succès.
     */
    public function invalidateToken(string $token): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = :token");
            return $stmt->execute([':token' => $token]);
        } catch (PDOException $e) {
            error_log('Erreur invalidateToken : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime tous les tokens de réinitialisation d'un utilisateur spécifique.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return bool true en cas de succès.
     */
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
     * Vérifie les identifiants et retourne l'utilisateur si OK.
     *
     * @param string $email Email fourni.
     * @param string $password Mot de passe fourni.
     * @return array<string, mixed>|null Le tableau utilisateur si valide, null sinon.
     */
    public function verifyLogin(string $email, string $password): ?array {
        $user = $this->getUserByEmail($email);
        if ($user && isset($user['password']) && is_string($user['password']) && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    /**
     * Logique centralisée de validation de la force du mot de passe.
     *
     * @param string $password Mot de passe à analyser.
     * @return bool Vrai si le mot de passe respecte les critères de sécurité.
     */
    public static function isPasswordStrong(string $password): bool {
        return mb_strlen($password) >= 8 
            && preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
    }

    /**
     * Génère un token de réinitialisation pour l'email donné.
     *
     * @param string $email L'email de l'utilisateur concerné.
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

        $idRaw = $user['id'] ?? 0;
        if (!is_numeric($idRaw)) {
            return null;
        }

        $userId = (int) $idRaw;

        $this->deleteResetTokensForUser($userId);
        $this->storeResetToken($userId, $hashedToken, $expiresAt);

        return ['token' => $token, 'user' => $user];
    }

    /**
     * Supprime un utilisateur par son ID.
     * 
     * @param int $id Identifiant de l'utilisateur à supprimer.
     * @return bool true en cas de succès, dale sinon.
     */
    public function deleteUser(int $id): bool {
        try {
            $this->deleteResetTokensForUser($id);
            
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }

}