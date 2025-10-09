<?php
class UserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Vérifier si un email existe déjà
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    // Enregistre un nouvel utilisateur
    public function register($first_name, $last_name, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$first_name, $last_name, $email, $hashedPassword]);
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Stocke un token pour l'utilisateur (supprime d'abord les anciens)
    public function storeResetToken(int $userId, string $token): bool {
        try {
            // Supprimer les anciens tokens de l'utilisateur
            $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$userId]);

            // On stocke directement le token brut (pas de hash)
            $stmt = $this->pdo->prepare(
                "INSERT INTO password_resets (user_id, token, created_at) VALUES (?, ?, NOW())"
            );
            return $stmt->execute([$userId, $token]);

        } catch (PDOException $e) {
            error_log("Erreur storeResetToken : " . $e->getMessage());
            return false;
        }
    }

    // Récupère la ligne password_resets si token valide
    public function findResetByToken(string $token, int $minutesValid = 30) {
        // On cherche le token brut (non haché)
        $stmt = $this->pdo->prepare(
            "SELECT pr.*, u.email FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token = ? 
            AND pr.created_at >= (NOW() - INTERVAL ? MINUTE)
            LIMIT 1"
        );
        $stmt->execute([$token, $minutesValid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Supprime les tokens d'un utilisateur
    public function deleteResetTokens(int $userId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    // Met à jour le mot de passe d'un user
    public function updatePassword(int $userId, string $hashedPassword): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }
}
?>
