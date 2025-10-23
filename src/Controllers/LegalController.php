<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Views\Shared\{Header, Footer, Layout};

final class LegalController
{
    public function mentions(): void
    {
        (new Layout(new Header(), new Footer()))
            ->render(__DIR__ . '/../Views/Legal/Mentions.php', 'Mentions légales');
    }
}
