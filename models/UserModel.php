<?php
class UserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($username, $email, $password) {
       //A faire
    }

    public function login($email, $password) {
        //A faire
    }
}
?>
