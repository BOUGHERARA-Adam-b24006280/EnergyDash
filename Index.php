<?php

$initRooter = "Index.php";

require_once "controllers/HomeController.php";
$controller = new HomeController();
$controller->index();

require_once "controllers/RequestController.php";
$requestController = new RequestController();
$requestController->TreatRequest();