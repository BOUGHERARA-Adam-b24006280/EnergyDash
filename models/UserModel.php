<?php
class UserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

        // Vérifier si un email existe déjà
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function register($first_name,$last_name, $email, $password) {
        $hashedPassword = md5($password);
        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$first_name, $last_name, $email, $hashedPassword]);
    }

    public function login($email, $password) {
        //A faire
    }
}
?>
