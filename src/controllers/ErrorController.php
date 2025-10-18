<?php

class ErrorController {
    public function notFound() {
        echo "<h1>404 - Page introuvable</h1>";
        echo "<a href='/'>Retour à l’accueil</a>";
    }
}