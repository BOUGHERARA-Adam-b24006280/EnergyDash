<?php
declare(strict_types=1);

namespace App\Controllers;
use App\Core\Layout;

final class LegalController
{
    public function mentions(): void
    {
        $layout = new Layout(__DIR__ . '/../Views/Legal/Mentions.php', 'Mentions Légales');
        $layout->render();
    }
}
