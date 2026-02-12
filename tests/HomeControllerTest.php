<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\HomeController;
use App\Core\View;

class HomeControllerTest extends TestCase {

    public function testIndexAfficheLaPageDAccueil() {
        $controller = $this->getMockBuilder(HomeController::class)->disableOriginalConstructor()->getMock();

        $viewMock = $this->createMock(View::class);
        $viewMock->expects($this->once())->method('render')->with(
                       $this->equalTo('home/index'),
                       $this->callback(function($data) {
                           return is_array($data) && 
                                  isset($data['title']) && 
                                  $data['title'] === 'Accueil';
                       })
                   );

        $reflection = new ReflectionClass(HomeController::class);
        $property = $reflection->getProperty('view');
        $property->setAccessible(true);
        $property->setValue($controller, $viewMock);

        $controller->index();
    }
}