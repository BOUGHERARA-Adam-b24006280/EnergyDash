<?php

namespace App\Views\shared;

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
        <html lang="fr" data-bs-theme="<?= $_COOKIE['theme'] ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars(($title ? "$title — EnergyDash" : "EnergyDash")) ?></title>
            <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
            <link rel="stylesheet" href="assets/css/Shared.css">
            <link rel="stylesheet" href="assets/css/VarColors.css">
        </head>
        <body>
            <?php $this->header->render(); ?>

            <main>
                <?php require $viewPath; ?>
            </main>

            <?php $this->footer->render(); ?>

            <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
    <?php
    }
}
