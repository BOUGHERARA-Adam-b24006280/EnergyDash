<?php
/**
 * Fichier : UserModel.php
 * Rôle : gérer les interactions avec la tables "users" dans la BDD
 * Auteur : Lucas L
 */

Use config\Database;
Use PDO;

/**
 * Classe UserModel
 * 
 * Ce modèle s'occupe des opérations liées aux utilisateurs, soit :
 * - Recherche de tous les utilisateurs
 * - Recherche par email
 * - Création d'un nouvell utilisateur
 */
Class UserModel {
    /**
     * Récupère tous les utilisateur de la BDD
     * 
     * @return array contenant tous les utilisateurs
     */
    Public static function getAllUsers(): array {
        $pdo = Database::getConnection();

    }

}