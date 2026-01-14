<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Layout;

class LayoutTest extends TestCase
{
    /**
     * S'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }
    }

    /**
     * Test simple du getter et du constructeur.
     */
    public function testGetTitle(): void
    {
        $layout = new Layout('some/path/view.php', 'Mon Super Titre');
        $this->assertEquals('Mon Super Titre', $layout->getTitle());
    }

    /**
     * Test principal : Vérifie que render() affiche bien la vue et passe les données.
     */
    public function testRenderDisplaysContentWithData(): void
    {
        $tempViewContent = '<div>Contenu de la vue : <?= $message ?></div>';
        $tempViewPath = sys_get_temp_dir() . '/test_view_' . uniqid() . '.php';
        file_put_contents($tempViewPath, $tempViewContent);

        try {
            $layout = new Layout($tempViewPath, 'Titre Test');

            ob_start();
            
            $layout->render(['message' => 'Hello PHPUnit']);
            
            $output = ob_get_clean();

            $this->assertStringContainsString('Hello PHPUnit', $output);
            
            $this->assertStringContainsString('Contenu de la vue', $output);

        } finally {
            if (file_exists($tempViewPath)) {
                unlink($tempViewPath);
            }
        }
    }
}