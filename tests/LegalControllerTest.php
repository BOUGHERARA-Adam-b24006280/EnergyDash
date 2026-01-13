<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\LegalController;

class LegalControllerTest extends TestCase {

    public function testIndexAfficheLesMentionsLegales() {
        $controller = $this->getMockBuilder(LegalController::class)->disableOriginalConstructor()->onlyMethods(['render'])->getMock();

        $controller->expects($this->once())->method('render')->with(
                       // Argument 1 : Le chemin de la vue doit être 'legal/mentions'
                       $this->equalTo('legal/mentions'),
                       
                       // Argument 2 : Les données passées doivent contenir le bon titre
                       $this->callback(function($data) {
                           return is_array($data) && 
                                  isset($data['title']) && 
                                  $data['title'] === 'Mentions Légales';
                       })
                   );

        $controller->index();
    }
}