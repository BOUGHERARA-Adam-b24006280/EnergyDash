<?php
/**
 * Fichier : DatabaseTest.php
 * Rôle : Tests Unitaires PHPUnit de \App\Core\Database.php.
 * Auteur : Lucas LEPAPE
 */

/**
 * Classe DatabaseTest qui teste \App\Core\Database.php.
 * Hérite de PHPUnit\Framework\TestCase pour utiliser les asserts.
 */
class DatabaseTest extends \PHPUnit\Framework\TestCase {
    /** @var \App\Core\Database pour tester la classe. */
    private \App\Core\Database $database;

    /** Initialise une nouvelle instance de \App\Core\Database. */
    protected function setUp(): void {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
        $this->database = new \App\Core\Database();
    }

    /**
     * Vérifie que getConnection() retourne bien une instance PDO.
     */
    public function testGetConnectionReturnsPDO() {
        $pdo = $this->database->getConnection();
        $this->assertInstanceOf(PDO::class, $pdo);
    }    

    /**
     * Vérifie que la connexion PDO est unique.
     */
    public function testGetConnectionIsUnique() {
        $pdo1 = $this->database->getConnection();
        $pdo2 = $this->database->getConnection();

        $this->assertSame($pdo1, $pdo2);
    }
}