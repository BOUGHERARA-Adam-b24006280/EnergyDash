<?php

/**
 * Fichier : DatabaseTest.php
 * Rôle : Tests Unitaires PHPUnit de Database.php.
 * Auteur : Lucas LEPAPE
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../config/Database.php';

/**
 * Classe DatabaseTest qui test Database.php.
 * Hérite de PHPUnit\Framework\TestCase pour utiliser les asserts.
 */
class DatabaseTest extends TestCase
{
    /** @var Database pour tester la classe. */
    private Database $database;

    /** Initialise une nouvelle instance de Database. */
    protected function setUp(): void
    {
        $this->database = new Database();
    }

    /**
     * Vérifie que getConnection() retourne bien une instance PDO.
     */
    public function testGetConnectionReturnsPDO()
    {
        $pdo = $this->database->getConnection();
        $this->assertInstanceOf(PDO::class, $pdo);
    }    

    /**
     * Vérifie que la connexion PDO est unique, en renvoyant donc deux mêmes objects
     */
    public function testGetConnectionIsUnique()
    {
        $pdo1 = $this->database->getConnection();
        $pdo2 = $this->database->getConnection();

        $this->assertSame($pdo1, $pdo2);
    }
}