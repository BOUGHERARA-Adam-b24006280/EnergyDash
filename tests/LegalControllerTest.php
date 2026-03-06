<?php

class LegalControllerTest extends \PHPUnit\Framework\TestCase {

    public function testIndexAfficheLesMentionsLegales() {
        $controller = (new ReflectionClass(\App\Controllers\LegalController::class))->newInstanceWithoutConstructor();

        $viewMock = $this->createMock(\App\Core\View::class);
        $viewMock->expects($this->once())->method('render')->with(
                       $this->equalTo('legal/mentions'),
                       $this->callback(function($data) {
                           return is_array($data) && 
                                  isset($data['title']) && 
                                  $data['title'] === 'Mentions Légales';
                       })
                   );

        $reflection = new ReflectionClass(\App\Controllers\LegalController::class);
        $property = $reflection->getProperty('view');
        $property->setAccessible(true);
        $property->setValue($controller, $viewMock);

        $controller->index();
    }
}