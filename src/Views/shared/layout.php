<?php
/**
 * Fichier : Layout.php
 * Rôle : Gère la configuration HTML des pages.
 * Auteur : Lucas LEPAPE,
 */

namespace App\Views\Shared;

class Layout {
    private Header $header;
    private Footer $footer;

    public function __construct(Header $header, Footer $footer) {
        $this->header = $header;
        $this->footer = $footer;
    }

    public function render(string $viewPath, ?string $title = "EnergyDash"): void {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars(($title ? "$title — EnergyDash" : "EnergyDash")) ?></title>
            <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
            <link rel="stylesheet" href="/assets/css/style.css">
        </head>
        <body class="bg-dark text-light">
            <?php $this->header->render(); ?>

            <main class="container mt-4 mb-5">
                <?php require $viewPath; ?>
            </main>

            <?php $this->footer->render(); ?>

            <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
}
