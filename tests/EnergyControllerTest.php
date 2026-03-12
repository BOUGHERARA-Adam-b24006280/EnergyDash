<?php

namespace Tests\Controllers;

use App\Controllers\EnergyController;
use PHPUnit\Framework\TestCase;

/**
 * Test unitaire pour EnergyController.
 * Adapte les tests pour correspondre à la nouvelle structure utilisant FileUploadService
 * et la gestion des algorithmes.
 */
class EnergyControllerTest extends TestCase {
    private $controller;
    private string $storagePath;

    protected function setUp(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Chemin vers le stockage pour vérifier les fichiers créés (ex: active_algo.txt)
        $this->storagePath = __DIR__ . '/../src/Storage';

        // On mocke le contrôleur pour intercepter les redirections et les messages flash
        $this->controller = $this->getMockBuilder(EnergyController::class)
            ->onlyMethods(['redirect', 'flash', 'requireLogin', 'validateCsrf'])
            ->getMock();
    }

    /**
     * Test : setAlgorithm enregistre correctement le choix de l'utilisateur.
     */
    public function testSetAlgorithmSavesCorrectValue(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['algo'] = 'lstm';
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin'];

        // On s'attend à ce que la sécurité soit vérifiée
        $this->controller->expects($this->once())->method('requireLogin');
        $this->controller->expects($this->once())->method('validateCsrf');
        
        // On s'attend à un message de succès
        $this->controller->expects($this->once())
            ->method('flash')
            ->with('success', $this->stringContains('mis à jour'));

        // On s'attend à une redirection vers le dashboard
        $this->controller->expects($this->once())
            ->method('redirect')
            ->with('/dashboard');

        $this->controller->setAlgorithm();
        
        // Vérification physique du fichier (si les permissions le permettent dans l'environnement de test)
        $algoFile = __DIR__ . '/../src/Storage/active_algo.txt';
        if (file_exists($algoFile)) {
            $this->assertEquals('lstm', trim((string)file_get_contents($algoFile)));
        }
    }

    /**
     * Test : setAlgorithm refuse un algorithme inconnu.
     */
    public function testSetAlgorithmRejectsInvalidAlgo(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['algo'] = 'algorithme_fantome';

        $this->controller->expects($this->once())
            ->method('flash')
            ->with('error', 'Algorithme non reconnu.');

        $this->controller->setAlgorithm();
    }

    /**
     * Test : upload échoue si l'utilisateur n'est pas identifié.
     */
    public function testUploadFailsWhenNoUserIdInSession(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['user'] = ['role' => 'editor']; // Pas d'ID
        $_FILES['csv_file'] = [
            'name' => 'test.csv',
            'type' => 'text/csv',
            'tmp_name' => '/tmp/phpabc123',
            'error' => 0,
            'size' => 1024
        ];

        $this->controller->expects($this->once())
            ->method('flash')
            ->with('error', 'Utilisateur non identifié.');

        $this->controller->upload();
    }

    /**
     * Test : delete affiche une erreur si aucun fichier n'existe pour l'utilisateur.
     */
    public function testDeleteFailsIfNoFileExists(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['user'] = ['id' => 9999, 'role' => 'user']; // ID inexistant

        $this->controller->expects($this->once())
            ->method('flash')
            ->with('error', $this->stringContains('Aucun fichier à supprimer'));

        $this->controller->delete();
    }
}