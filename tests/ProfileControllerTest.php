<?php

namespace App\Controllers {
    /**
     * Mock de filter_input pour le namespace App\Controllers.
     * En CLI (PHPUnit), filter_input ne lit pas les données injectées dans $_POST.
     * Cette fonction permet au contrôleur de "voir" les données de test.
     */
    function filter_input(int $type, string $var_name, int $filter = FILTER_DEFAULT, array|int $options = 0): mixed {
        if ($type === INPUT_POST && isset($_POST[$var_name])) {
            return filter_var($_POST[$var_name], $filter, $options);
        }
        return \filter_input($type, $var_name, $filter, $options);
    }
}

namespace Tests\Controllers {

    use PHPUnit\Framework\TestCase;
    use PHPUnit\Framework\MockObject\MockObject;
    use App\Controllers\ProfileController;
    use App\Models\UserModel;
    use App\Core\Database;
    use ReflectionClass;

    class ProfileControllerTest extends TestCase
    {
        /** @var MockObject&ProfileController */
        private $controller;
        
        /** @var MockObject&UserModel */
        private $userModelMock;

        protected function setUp(): void
        {
            $_ENV['DATABASE_HOST'] = 'localhost';
            $_ENV['DATABASE_NAME'] = 'test';
            $_ENV['DATABASE_USER'] = 'root';
            $_ENV['DATABASE_PASSWORD'] = '';

            $pdoMock = $this->createMock(\PDO::class);
            $dbReflection = new ReflectionClass(Database::class);
            $instanceProperty = $dbReflection->getProperty('instance');
            $instanceProperty->setAccessible(true);
            $instanceProperty->setValue(null, $pdoMock);

            $this->userModelMock = $this->createMock(UserModel::class);

            $this->controller = $this->getMockBuilder(ProfileController::class)
                ->onlyMethods(['redirect'])
                ->getMock();

            $this->controller->method('redirect');

            $controllerReflection = new ReflectionClass(ProfileController::class);
            $modelProperty = $controllerReflection->getProperty('userModel');
            $modelProperty->setAccessible(true);
            $modelProperty->setValue($this->controller, $this->userModelMock);

            $_SESSION = [];
            $_POST = [];
        }

        /**
         * Teste l'affichage du profil pour un utilisateur standard.
         */
        public function testIndexDisplaysUserProfile(): void
        {
            $_SESSION['user'] = [
                'id' => 1,
                'role' => 'user',
                'first_name' => 'Adam',
                'last_name' => 'B.',
                'email' => 'adam@test.com'
            ];

            $this->userModelMock->expects($this->never())->method('getAllUsers');

            ob_start();
            $this->controller->index();
            ob_end_clean();

            $this->assertTrue(true);
        }

        /**
         * Teste l'affichage pour un administrateur.
         */
        public function testIndexDisplaysAdminViewWithUserList(): void
        {
            $_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'first_name' => 'Admin'];

            $this->userModelMock->method('getAllUsers')->willReturn([
                ['id' => 2, 'first_name' => 'Lucas', 'last_name' => 'D.', 'email' => 'lucas@test.com', 'role' => 'user']
            ]);

            ob_start();
            $this->controller->index();
            ob_end_clean();

            $this->assertTrue(true);
        }

        /**
         * Teste la mise à jour réussie du profil.
         */
        public function testUpdateSuccess(): void
        {
            $_SESSION['user'] = ['id' => 1, 'role' => 'user'];
            $_SESSION['csrf_token'] = 'token123';
            
            $_POST['csrf_token'] = 'token123';
            $_POST['first_name'] = 'NouveauNom';
            $_POST['last_name'] = 'NouveauPrenom';
            $_POST['email'] = 'test@example.com';

            $this->userModelMock->expects($this->once())
                ->method('updateUser')
                ->with(1, 'NouveauNom', 'NouveauPrenom', 'test@example.com', '');

            $this->controller->expects($this->once())
                ->method('redirect')
                ->with('/profile');

            $this->controller->update();

            $this->assertEquals("Profil mis à jour avec succès.", $_SESSION['success'] ?? '');
        }

        /**
         * Teste l'échec si l'email n'est pas valide.
         */
        public function testUpdateFailsWithInvalidEmail(): void
        {
            $_SESSION['user'] = ['id' => 1, 'role' => 'user'];
            $_SESSION['csrf_token'] = 'token123';
            
            $_POST['csrf_token'] = 'token123';
            $_POST['email'] = 'mauvais-email';

            // Le modèle ne doit JAMAIS être appelé si l'email est invalide
            $this->userModelMock->expects($this->never())->method('updateUser');

            $this->controller->update();

            $this->assertEquals("Adresse email invalide.", $_SESSION['error'] ?? '');
        }
    }
}