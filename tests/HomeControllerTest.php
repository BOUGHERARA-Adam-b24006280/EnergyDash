<?php

class HomeControllerTest extends \PHPUnit\Framework\TestCase {

    public function testIndexAfficheLaPageDAccueil() {
        $controller = (new ReflectionClass(\App\Controllers\HomeController::class))->newInstanceWithoutConstructor();

        $viewMock = $this->createMock(\App\Core\View::class);
        $viewMock->expects($this->once())->method('render')->with(
                       $this->equalTo('home/index'),
                       $this->callback(function($data) {
                           return is_array($data) && 
                                  isset($data['title']) && 
                                  $data['title'] === 'Accueil';
                       })
                   );

        $reflection = new ReflectionClass(\App\Controllers\HomeController::class);
        $property = $reflection->getProperty('view');
        $property->setAccessible(true);
        $property->setValue($controller, $viewMock);

        $controller->index();
    }
}