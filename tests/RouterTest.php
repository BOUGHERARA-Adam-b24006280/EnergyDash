<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Router;

// --- Mocks ---
class MockController
{
    public function index(): void
    {
        echo "Index Called";
    }

    public function postAction(): void
    {
        echo "Post Called";
    }
}

if (!class_exists('App\Controllers\ErrorController')) {
    eval('
        namespace App\Controllers;
        class ErrorController {
            public function error404page() {
                echo "404 Not Found";
            }
        }
    ');
}

/**
 * Classe de test pour le Router
 */
class RouterTest extends TestCase
{
    private Router $router;

    /**
     * S'exécute avant chaque test.
     * On remet le Router à neuf et on nettoie les superglobales.
     */
    protected function setUp(): void
    {
        $this->router = new Router();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
    }

    /**
     * Cas 1 : Test d'une route GET simple qui fonctionne.
     */
    public function testDispatchExecutesGetRoute(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $this->router->add('GET', '/home', [MockController::class, 'index']);

        $this->expectOutputString('Index Called');

        $this->router->dispatch();
    }

    /**
     * Cas 2 : Test d'une route POST.
     */
    public function testDispatchExecutesPostRoute(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/submit';

        $this->router->add('POST', '/submit', [MockController::class, 'postAction']);

        $this->expectOutputString('Post Called');
        $this->router->dispatch();
    }

    /**
     * Cas 3 : Test de la gestion du slash final (Trailing slash).
     */
    public function testDispatchRemovesTrailingSlash(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/about/';

        $this->router->add('GET', '/about', [MockController::class, 'index']);

        $this->expectOutputString('Index Called');
        $this->router->dispatch();
    }

    /**
     * Cas 4 : Test de la page 404.
     * Si aucune route ne correspond, le ErrorController doit être appelé.
     */
    public function testDispatchTriggers404WhenNoRouteMatches(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/not-existing-page';

        $this->expectOutputRegex('/404/');
        $this->router->dispatch();
    }

    /**
     * Cas 5 : Exception si la classe du contrôleur n'existe pas.
     */
    public function testDispatchThrowsExceptionIfControllerNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/bad-controller';

        $this->router->add('GET', '/bad-controller', ['GhostController', 'index']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Unable to load class: GhostController");

        $this->router->dispatch();
    }

    /**
     * Cas 6 : Exception si la méthode n'existe pas dans le contrôleur.
     */
    public function testDispatchThrowsExceptionIfMethodNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/bad-method';

        $this->router->add('GET', '/bad-method', [MockController::class, 'unknownMethod']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Method not callable or not found: unknownMethod");

        $this->router->dispatch();
    }
}