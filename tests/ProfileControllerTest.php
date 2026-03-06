<?php

// ---------------------------------------------------------
// BLOCK 1 : MOCK DES FONCTIONS NATIVES
// ---------------------------------------------------------
// filter_input ne fonctionne pas bien en CLI (ligne de commande) avec PHPUnit.
// On le redéfinit DANS le namespace du contrôleur pour qu'il lise directement $_POST.
namespace App\Controllers;

function filter_input(int $type, string $variable_name, int $filter = FILTER_DEFAULT): mixed {
    if ($type === INPUT_POST) {
        $val = $_POST[$variable_name] ?? null;
        // Simulation simplifiée : si c'est un email valide demandé et que la valeur ressemble à un email
        if ($filter === FILTER_VALIDATE_EMAIL && $val && !str_contains($val, '@')) {
            return false;
        }
        if ($filter === FILTER_VALIDATE_INT && $val && !is_numeric($val)) {
            return false;
        }
        return $val;
    }
    return null;
}

// ---------------------------------------------------------
// BLOCK 2 : LA CLASSE DE TEST
// ---------------------------------------------------------
namespace Tests\Controllers;


class ProfileControllerTest extends \PHPUnit\Framework\TestCase {
    private $controller;
    private $userModelMock;
    private $viewMock;

    protected function setUp(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $_POST = [];

        $this->userModelMock = $this->createMock(\App\Models\UserModel::class);
        $this->viewMock = $this->createMock(\App\Core\View::class);

        $this->controller = $this->getMockBuilder(\App\Controllers\ProfileController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['redirect', 'flash', 'requireLogin', 'requireAdmin'])
            ->getMock();

        $reflection = new \ReflectionClass(\App\Controllers\ProfileController::class);
        
        $property = $reflection->getProperty('userModel');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->userModelMock);

        $property = $reflection->getProperty('view');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->viewMock);
    }

    /**
     * Test : index() affiche le profil standard pour un utilisateur lambda.
     */
    public function testIndexRendersStandardProfileForUser(): void {
        $_SESSION['user'] = ['id' => 1, 'role' => 'user', 'first_name' => 'John'];

        $this->userModelMock->expects($this->never())->method('getAllUsers');

        $this->viewMock->expects($this->once())
            ->method('render')
            ->with(
                'profile/profile',
                $this->callback(function ($data) {
                    return $data['title'] === 'Mon Profil' && $data['user']['first_name'] === 'John';
                })
            );

        $this->controller->index();
    }

    /**
     * Test : index() affiche le profil ADMIN et charge la liste des utilisateurs.
     */
    public function testIndexRendersAdminProfileWithUsersList(): void {
        // Contexte : Admin connecté
        $_SESSION['user'] = ['id' => 99, 'role' => 'admin'];

        $this->userModelMock->expects($this->once())
            ->method('getAllUsers')
            ->willReturn([['id' => 1, 'name' => 'Alice'], ['id' => 2, 'name' => 'Bob']]);

        $this->viewMock->expects($this->once())
            ->method('render')
            ->with('profile/profile_admin', $this->anything());

        $this->controller->index();
    }

    /**
     * Test : update() redirige si pas connecté (Sécurité).
     */
    public function testUpdateRedirectsIfNotLogged(): void {
        unset($_SESSION['user']);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->with('/login');

        $this->controller->update();
    }

    /**
     * Test : update() met à jour le profil avec succès.
     */
    public function testUpdateSuccess(): void {
        $_SESSION['user'] = ['id' => 10, 'email' => 'old@test.com', 'first_name' => 'Old', 'last_name' => 'OldName'];
        
        $_POST['email'] = 'new@test.com';
        $_POST['first_name'] = 'New';
        $_POST['last_name'] = 'Name';
        $_POST['password'] = '1234';

        $this->userModelMock->expects($this->once())
            ->method('updateUser')
            ->with(10, 'New', 'Name', 'new@test.com', '1234')
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('flash')
            ->with('success');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->with('/profile');

        $this->controller->update();

        $this->assertEquals('new@test.com', $_SESSION['user']['email']);
    }

    /**
     * Test : updateRole() refuse qu'un admin se modifie lui-même.
     */
    public function testUpdateRolePreventsSelfModification(): void {
        $_SESSION['user'] = ['id' => 5, 'role' => 'admin'];

        $_POST['id'] = 5;
        $_POST['role'] = 'user';

        $this->userModelMock->expects($this->never())->method('updateUserRole');

        $this->controller->expects($this->once())
            ->method('flash')
            ->with('error', $this->stringContains('propre rôle'));

        $this->controller->updateRole();
    }

    /**
     * Test : updateRole() fonctionne sur un autre utilisateur.
     */
    public function testUpdateRoleSuccessOnOtherUser(): void {
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin'];

        $_POST['id'] = 2;
        $_POST['role'] = 'editor';

        $this->userModelMock->expects($this->once())
            ->method('updateUserRole')
            ->with(2, 'editor')
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('flash')
            ->with('success');

        $this->controller->updateRole();
    }
}