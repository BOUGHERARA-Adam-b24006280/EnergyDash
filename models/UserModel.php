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
        // Enregiste un nouvel utilisateur
    public function register($first_name,$last_name, $email, $password) {
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
            // Supprimer les anciens tokens
            $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Créer un hash du token
            $tokenHash = hash_hmac('sha256', $token, 'ENERGYDASH_SECRET');

            // Insertion du nouveau token
            $stmt = $this->pdo->prepare(
                "INSERT INTO password_resets (user_id, token, created_at) VALUES (?, ?, NOW())"
            );

            $ok = $stmt->execute([$userId, $tokenHash]);

            if (!$ok) {
                $err = $stmt->errorInfo();
                error_log("[storeResetToken] INSERT échoué : " . implode(' | ', $err));
            } else {
                error_log("[storeResetToken] Token ajouté pour user_id={$userId}");
            }

            return $ok;
        } catch (PDOException $e) {
            error_log("storeResetToken] Exception PDO : " . $e->getMessage());
            return false;
        }
    }





        // Récupère la ligne password_resets si token valide
    public function findResetByToken(string $token, int $minutesValid = 30) {
        $tokenHash = hash_hmac('sha256', $token, 'ENERGYDASH_SECRET');
        $stmt = $this->pdo->prepare(
            "SELECT pr.*, u.email FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token = ? AND pr.created_at >= (NOW() - INTERVAL ? MINUTE) LIMIT 1"
        );
        $stmt->execute([$tokenHash, $minutesValid]);
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
