<?php

namespace App\Core;

abstract class Controller {
    /**
     * @param array<string, mixed> $data Données passées à la vue
     */
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