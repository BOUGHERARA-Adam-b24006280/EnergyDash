<?php

class AuthSystemTest extends \PHPUnit\Framework\TestCase {
    private \App\Models\UserModel $userModel;
    private string $testEmail = 'test_auth_sys_verify@example.com';
    private string $testPassword = 'Password123@System';

    public static function setUpBeforeClass(): void {
        // Load env variables
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }

    protected function setUp(): void {
        $this->userModel = new \App\Models\UserModel();
        $this->cleanup();
    }

    protected function tearDown(): void {
        $this->cleanup();
    }

    private function cleanup(): void{
        $db = (new \App\Core\Database())->getConnection();
        try {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $this->testEmail]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $db->prepare("DELETE FROM password_resets WHERE user_id = :id")->execute([':id' => $user['id']]);
                $db->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $user['id']]);
            }
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    public function testAuthFlow() {
        $firstName = 'Test';
        $lastName = 'User';
        
        // emailExists
        $this->assertFalse($this->userModel->emailExists($this->testEmail), "L'email ne devrait pas exister au début");

        // createUser
        $created = $this->userModel->createUser($firstName, $lastName, $this->testEmail, $this->testPassword);
        $this->assertTrue($created, "L'utilisateur devrait être créé");

        // emailExists
        $this->assertTrue($this->userModel->emailExists($this->testEmail), "L'email devrait exister après création");

        // Connexion
        $user = $this->userModel->getUserByEmail($this->testEmail);
        $this->assertNotNull($user, "L'utilisateur devrait être récupérable par email");
        $this->assertEquals($this->testEmail, $user['email']);
        
        // Verif hash
        $this->assertTrue(password_verify($this->testPassword, $user['password']), "Le mot de passe doit correspondre au hash");
        $this->assertFalse(password_verify('WrongPass', $user['password']), "Un mauvais mot de passe ne doit pas passer");

        // Mot de passe oublié (Token)
        $tokenRaw = bin2hex(random_bytes(32));
        $tokenHashed = hash('sha256', $tokenRaw);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $stored = $this->userModel->storeResetToken($user['id'], $tokenHashed, $expiresAt);
        $this->assertTrue($stored, "Le token de reset doit être stocké");

        // Verification Token (Reset Page)
        $userByToken = $this->userModel->getUserByToken($tokenHashed);
        $this->assertNotNull($userByToken, "L'utilisateur doit être trouvé via le token valide");
        $this->assertEquals($user['id'], $userByToken['id']);

        // Modification mot de passe
        $newPass = 'NewSecurePass123!';
        $updated = $this->userModel->updatePassword($user['id'], $newPass);
        $this->assertTrue($updated, "Le mot de passe doit être mis à jour");

        // Invalidation token
        $invalidated = $this->userModel->invalidateToken($tokenHashed);
        $this->assertTrue($invalidated, "Le token doit être invalidé");

        $userByTokenAgain = $this->userModel->getUserByToken($tokenHashed);
        $this->assertNull($userByTokenAgain, "Le token ne doit plus être valide");

        // Verification nouvelle connexion
        $userReloaded = $this->userModel->getUserByEmail($this->testEmail);
        $this->assertTrue(password_verify($newPass, $userReloaded['password']), "Le nouveau mot de passe doit fonctionner");
        $this->assertFalse(password_verify($this->testPassword, $userReloaded['password']), "L'ancien mot de passe ne doit plus fonctionner");
    }
}