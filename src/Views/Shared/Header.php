<?php
/**
 * Fichier : Header.php
 * Rôle : Gère le header des pages.
 * Auteur : Lucas LEPAPE, Gustin MAILHÉ
 */

namespace App\Views\Shared;

class Header {
    private string $title = "EnergyDash";

    public function render(): void {
        ?>
        <header>
          <a href="/"><?php echo $this->title ?></a>

          <form method="POST" action="" style="display: inline;">
              <input type="hidden" name="action" value="switchTheme">
              <button type="submit" id="theme-toggle"><?php echo $_COOKIE['toggleTheme'];?></button>
          </form>

          <div class="d-flex align-items-center">
              <a class="btn btn-outline-primary pe-3 ps-3 fw-bold me-3" href="/login">Se connecter</a>
              <a class="btn btn-primary pe-4 ps-4 fw-bold" href="register">S'inscrire</a>
          </div>
        </header>
        <?php
    }
}
