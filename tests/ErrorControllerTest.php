<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\ErrorController;

class ErrorControllerTest extends TestCase {

    /**
     * Teste que la page 404 définit le bon code HTTP et appelle la bonne vue.
     */
    public function testError404Page() {
        $controller = $this->getMockBuilder(ErrorController::class)->onlyMethods(['render'])->getMock();

        $controller->expects($this->once())->method('render')->with(
                       $this->equalTo('error/404'), // Vérifie le chemin de la vue
                       $this->callback(function($data) {
                           // Vérifie le titre passé à la vue
                           return isset($data['title']) && $data['title'] === 'Page non trouvée';
                       })
                   );

        $controller->error404page();

        $this->assertEquals(404, http_response_code());
    }
}