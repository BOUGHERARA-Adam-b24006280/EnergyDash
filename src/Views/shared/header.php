<?php
/**
 * Fichier : Header.php
 * Rôle : Gère le header des pages.
 * Auteur : Lucas LEPAPE,
 */

namespace App\Views\Shared;

class Header {
    private string $title;

    public function __construct(string $title = "EnergyDash") {
        $this->title = $title;
    }

    public function render(): void {
        ?>
        <nav class="navbar navbar-expand-md bg-body-tertiary border-bottom">
            <div class="container-fluid">
                <a class="navbar-brand fw-semibold text-body-secondary" href="/">
                    <?= htmlspecialchars($this->title) ?>
                </a>
                <?php if (!isset($_SESSION['user'])): ?>
                    <div class="d-flex align-items-center">
                        <a class="btn btn-outline-primary pe-3 ps-3 fw-bold me-3" href="/login">
                            Se connecter
                        </a>
                        <a class="btn btn-primary pe-4 ps-4 fw-bold" href="/register">
                            S'inscrire
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-center">
                        <a class="btn btn-outline-danger pe-4 ps-4 fw-bold" href="/logout">
                            Déconnexion
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
        <?php
    }
}
