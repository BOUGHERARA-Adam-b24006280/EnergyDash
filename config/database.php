<?php
    $host = 'mysql-energydash.alwaysdata.net';   // Hôte visible dans ton panneau AlwaysData
    $dbname = 'energydash_db';                   // nom complet de la base (préfixé par ton ID)
    $username = '434284';                        // ton identifiant utilisateur MySQL
    $password = 'HechekUserDeLaBD_69La%$$)Trik'; // le mot de passe MySQL défini dans AlwaysData

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
?>
