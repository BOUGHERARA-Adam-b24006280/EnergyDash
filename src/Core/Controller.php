<?php
namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        $layoutPath = __DIR__ . '/../Views/shared/layout.php';

        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo "Vue introuvable : $viewPath";
            return;
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}