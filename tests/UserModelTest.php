<?php

namespace Tests\Models;

class UserModelTest extends \PHPUnit\Framework\TestCase {
    private $pdo;
    private \App\Models\UserModel $userModel;

    protected function setUp(): void {
        $this->pdo = $this->createMock(\PDO::class);

        $reflection = new \ReflectionClass(\App\Models\UserModel::class);
        $this->userModel = $reflection->newInstanceWithoutConstructor();

        $property = $reflection->getParentClass()->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->userModel, $this->pdo);


        $propTable = $reflection->getProperty('table');
        $propTable->setAccessible(true);
        $propTable->setValue($this->userModel, 'users');
    }

    /**
     * Test : emailExists renvoie true si la base trouve une ligne.
     */
    public function testEmailExistsReturnsTrueIfFound(): void {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('fetch')->willReturn(['id' => 1, 'email' => 'test@test.com']);

        $this->pdo->method('prepare')->willReturn($stmt);

        $exists = $this->userModel->emailExists('test@test.com');

        $this->assertTrue($exists);
    }

    /**
     * Test : createUser hash le mot de passe et appelle l'insertion.
     */
    public function testCreateUserHashesPasswordAndInserts(): void {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $this->pdo->method('lastInsertId')->willReturn('10');
        $this->pdo->method('prepare')->willReturn($stmt);

        $stmt->expects($this->once())
             ->method('execute')
             ->with($this->callback(function ($params) {
                 return $params['email'] === 'mohamed@test.com' 
                     && $params['password'] !== 'azerty'
                     && password_verify('azerty', $params['password']);
             }));

        $result = $this->userModel->createUser('Mohamed', 'Haddad', 'mohamed@test.com', 'azerty');

        $this->assertTrue($result);
    }

    /**
     * Test : verifyLogin renvoie l'utilisateur si le mot de passe est bon.
     */
    public function testVerifyLoginSuccess(): void {
        $realHash = password_hash('Secret123!', PASSWORD_DEFAULT);

        $userData = [
            'id' => 1,
            'email' => 'lucas@test.com',
            'password' => $realHash
        ];

        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('fetch')->willReturn($userData);
        $this->pdo->method('prepare')->willReturn($stmt);

        $user = $this->userModel->verifyLogin('lucas@test.com', 'Secret123!');

        $this->assertIsArray($user);
        $this->assertEquals('lucas@test.com', $user['email']);
    }

    /**
     * Test : verifyLogin échoue avec un mauvais mot de passe.
     */
    public function testVerifyLoginFailsWithWrongPassword(): void {
        $realHash = password_hash('BonMotDePasse', PASSWORD_DEFAULT);
        
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('fetch')->willReturn(['password' => $realHash]);
        $this->pdo->method('prepare')->willReturn($stmt);

        $user = $this->userModel->verifyLogin('test@test.com', 'MauvaisMdp');

        $this->assertNull($user);
    }

    /**
     * Test de la méthode statique isPasswordStrong.
     * Pas besoin de Mock ici car c'est une fonction pure.
     */
    public function testIsPasswordStrong(): void {
        $this->assertFalse(\App\Models\UserModel::isPasswordStrong('Short1!'));
        $this->assertFalse(\App\Models\UserModel::isPasswordStrong('weakpassword1!'));
        $this->assertFalse(\App\Models\UserModel::isPasswordStrong('NoNumber!'));
        $this->assertFalse(\App\Models\UserModel::isPasswordStrong('NoSpecialChar1'));
        
        $this->assertTrue(\App\Models\UserModel::isPasswordStrong('StrongPass1!'));
    }

    /**
     * Test : updateUserRole empêche les rôles invalides.
     */
    public function testUpdateUserRoleRefusesInvalidRole(): void {
        $this->pdo->expects($this->never())->method('prepare');

        $result = $this->userModel->updateUserRole(1, 'super_hacker');

        $this->assertFalse($result);
    }

    /**
     * Test : updateUserRole accepte les rôles valides.
     */
    public function testUpdateUserRoleAcceptsValidRole(): void {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->userModel->updateUserRole(1, 'admin');

        $this->assertTrue($result);
    }
}