<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use App\Core\View;

/**
 * Sous-classe pour exposer getFlash en public pour les tests.
 */
class TestableView extends View
{
    public function publicGetFlash(string $type): ?string
    {
        return $this->getFlash($type);
    }
}

class ViewTest extends TestCase
{
    /**
     * S'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    /**
     * Test : render() affiche bien la vue et passe les données.
     */
    public function testRenderDisplaysContentWithData(): void
    {
        // Crée un fichier vue temporaire
        $tempViewContent = '<div>Contenu de la vue : <?= $message ?></div>';
        $viewName = 'test_view_' . uniqid();
        $viewDir = __DIR__ . '/../src/Views/';
        $tempViewPath = $viewDir . $viewName . '.php';
        file_put_contents($tempViewPath, $tempViewContent);

        try {
            $view = new View();

            ob_start();
            $view->render($viewName, ['message' => 'Hello PHPUnit', 'title' => 'Titre Test']);
            $output = ob_get_clean();

            $this->assertStringContainsString('Hello PHPUnit', $output);
            $this->assertStringContainsString('Contenu de la vue', $output);
        } finally {
            if (file_exists($tempViewPath)) {
                unlink($tempViewPath);
            }
        }
    }

    /**
     * Test : getFlash récupère un message et le supprime de la session.
     */
    public function testGetFlashReturnsAndClearsMessage(): void
    {
        $_SESSION['success'] = 'Bravo !';

        $view = new TestableView();

        $msg = $view->publicGetFlash('success');
        $this->assertEquals('Bravo !', $msg);

        // Le message doit être supprimé après lecture
        $this->assertNull($view->publicGetFlash('success'));
    }

    /**
     * Test : getFlash retourne null si aucun message.
     */
    public function testGetFlashReturnsNullWhenEmpty(): void
    {
        $view = new TestableView();
        $this->assertNull($view->publicGetFlash('success'));
    }
}