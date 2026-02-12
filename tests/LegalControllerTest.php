<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\LegalController;
use App\Core\View;

class LegalControllerTest extends TestCase {

    public function testIndexAfficheLesMentionsLegales() {
        $controller = $this->getMockBuilder(LegalController::class)->disableOriginalConstructor()->getMock();

        $viewMock = $this->createMock(View::class);
        $viewMock->expects($this->once())->method('render')->with(
                       $this->equalTo('legal/mentions'),
                       $this->callback(function($data) {
                           return is_array($data) && 
                                  isset($data['title']) && 
                                  $data['title'] === 'Mentions Légales';
                       })
                   );

        $reflection = new ReflectionClass(LegalController::class);
        $property = $reflection->getProperty('view');
        $property->setAccessible(true);
        $property->setValue($controller, $viewMock);

        $controller->index();
    }
}