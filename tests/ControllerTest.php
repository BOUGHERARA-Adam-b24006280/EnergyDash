<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Controller;

/**
 * Classe concrète temporaire pour tester la classe abstraite Controller..
 */
class TestableController extends Controller
{
    public function publicSanitize(string $input): string
    {
        return $this->sanitize($input);
    }

    public function publicFlash(string $type, string $message): void
    {
        $this->flash($type, $message);
    }

    public function publicRequireLogin(): void
    {
        $this->requireLogin();
    }

    public function publicRequireAdmin(): void
    {
        $this->requireAdmin();
    }
}

class ControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    /**
     * Test de la méthode sanitize (protection XSS)
     */
    public function testSanitizeCleansInput(): void
    {
        $controller = new TestableController();
        
        $dirty = '<script>alert("hack")</script>';
        $clean = $controller->publicSanitize($dirty);

        $this->assertEquals('&lt;script&gt;alert(&quot;hack&quot;)&lt;/script&gt;', $clean);
    }

    /**
     * Test du système de messages Flash
     */
    public function testFlashMessages(): void
    {
        $controller = new TestableController();

        $controller->publicFlash('success', 'Bravo !');

        $this->assertEquals('Bravo !', $_SESSION['success']);
    }

    /**
     * Test : requireLogin redirige si l'utilisateur n'est PAS connecté.
     */
    public function testRequireLoginRedirectsWhenNotLogged(): void
    {
        $controller = $this->getMockBuilder(TestableController::class)->onlyMethods(['redirect'])->getMock();

        $controller->expects($this->once())->method('redirect')->with('/login');

        $controller->publicRequireLogin();
    }

    /**
     * Test : requireLogin ne fait rien si l'utilisateur EST connecté.
     */
    public function testRequireLoginDoesNothingWhenLogged(): void
    {
        $controller = $this->getMockBuilder(TestableController::class)->onlyMethods(['redirect'])->getMock();

        $controller->expects($this->never())->method('redirect');

        $_SESSION['user'] = ['id' => 1, 'name' => 'Toto'];

        $controller->publicRequireLogin();
    }

    /**
     * Test : requireAdmin redirige un utilisateur normal vers /profile.
     */
    public function testRequireAdminRedirectsSimpleUser(): void
    {
        $controller = $this->getMockBuilder(TestableController::class)
                           ->onlyMethods(['redirect'])
                           ->getMock();

        $controller->expects($this->once())->method('redirect')->with('/profile');

        $_SESSION['user'] = ['id' => 1, 'role' => 'user'];

        $controller->publicRequireAdmin();
    }

    /**
     * Test : requireAdmin laisse passer un admin.
     */
    public function testRequireAdminAllowsAdminUser(): void
    {
        $controller = $this->getMockBuilder(TestableController::class)->onlyMethods(['redirect'])->getMock();

        $controller->expects($this->never())->method('redirect');

        $_SESSION['user'] = ['id' => 1, 'role' => 'admin'];

        $controller->publicRequireAdmin();
    }
}