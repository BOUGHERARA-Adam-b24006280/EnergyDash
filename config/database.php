<?php
    $host = 'localhost';   // Hôte visible dans ton panneau AlwaysData
    $dbname = 'energy_dash';                   // nom complet de la base (préfixé par ton ID)
    $username = 'root';                        // ton identifiant utilisateur MySQL
    $password = ''; // le mot de passe MySQL défini dans AlwaysData

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
?>
