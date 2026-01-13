<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\HomeController;

class HomeControllerTest extends TestCase {

    public function testIndexAfficheLaPageDAccueil() {
        $controller = $this->getMockBuilder(HomeController::class)->disableOriginalConstructor()->onlyMethods(['render'])->getMock();

        $controller->expects($this->once())->method('render')->with(
                       // 1er argument attendu : le chemin de la vue
                       $this->equalTo('home/index'),
                       
                       // 2ème argument attendu : le tableau de données
                       $this->callback(function($data) {
                           // On vérifie que le titre est bien "Accueil"
                           return is_array($data) && 
                                  isset($data['title']) && 
                                  $data['title'] === 'Accueil';
                       })
                   );

        $controller->index();
    }
}