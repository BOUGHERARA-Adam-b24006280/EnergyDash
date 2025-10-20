<?php

namespace App\Core;

abstract class Controller {
    protected function render(string $view, array $data): void {
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("La vue $view est introuvable : $viewPath");
        }

        extract($data);
        require $viewPath;
    }

    protected function redirect(string $url): void {
        header('Location:'. $url);
        exit;
    }

    protected function setHttpCode(int $code): void {
        http_response_code($code);
    }

}