<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Model;
use PDO;
use PDOStatement;

/**
 * Classe concrète pour tester la classe abstraite Model.
 * On définit une table fictive 'users' pour les tests.
 */
class ConcreteModel extends Model
{
    protected string $table = 'users';
}

class ModelTest extends TestCase
{
    private $pdo;
    private $model;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);

        $this->model = new ConcreteModel($this->pdo);
    }

    /**
     * Test de la méthode findAll()
     */
    public function testFindAllReturnsArrayOfRows(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        
        $expectedData = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob']
        ];
        $stmt->method('fetchAll')->willReturn($expectedData);

        $this->pdo->method('query')
                  ->with("SELECT * FROM users")
                  ->willReturn($stmt);

        // Action
        $result = $this->model->findAll();

        // Assertion
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Alice', $result[0]['name']);
    }

    /**
     * Test de findById()
     */
    public function testFindByIdReturnsRowIfFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        
        $stmt->method('fetch')->willReturn(['id' => 10, 'name' => 'Charlie']);

        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->with($this->stringContains('WHERE id = :id'))
                  ->willReturn($stmt);

        $result = $this->model->findById(10);

        $this->assertNotNull($result);
        $this->assertEquals(10, $result['id']);
    }

    public function testFindByIdReturnsNullIfNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(false);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->model->findById(999);

        $this->assertNull($result);
    }

    /**
     * Test de create()
     */
    public function testCreateInsertsDataAndReturnsId(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);
        $this->pdo->method('lastInsertId')->willReturn('55');

        $data = ['name' => 'David', 'email' => 'david@test.com'];
        $newId = $this->model->create($data);

        $this->assertEquals(55, $newId);
    }

    /**
     * Test de update()
     */
    public function testUpdateReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('rowCount')->willReturn(1);

        $this->pdo->method('prepare')->willReturn($stmt);

        $success = $this->model->update(1, ['name' => 'David Modified']);

        $this->assertTrue($success);
    }

    /**
     * Test de delete()
     */
    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('rowCount')->willReturn(1);

        $this->pdo->method('prepare')->willReturn($stmt);

        $success = $this->model->delete(1);

        $this->assertTrue($success);
    }
}