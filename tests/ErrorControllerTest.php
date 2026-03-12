<?php

class ErrorControllerTest extends \PHPUnit\Framework\TestCase {

    /**
     * Teste que la page 404 définit le bon code HTTP et appelle la bonne vue.
     */
    public function testError404Page() {
        $controller = new \App\Controllers\ErrorController();

        $viewMock = $this->createMock(\App\Core\View::class);
        $viewMock->expects($this->once())->method('render')->with(
                       $this->equalTo('error/404'),
                       $this->callback(function($data) {
                           return isset($data['title']) && $data['title'] === 'Page non trouvée';
                       })
                   );

        $reflection = new ReflectionClass(\App\Controllers\ErrorController::class);
        $property = $reflection->getProperty('view');
        $property->setAccessible(true);
        $property->setValue($controller, $viewMock);

        $controller->error404page();

        $this->assertEquals(404, http_response_code());
    }
}