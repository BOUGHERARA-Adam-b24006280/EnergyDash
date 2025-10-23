<?php
/**
 * Fichier : Footer.php
 * Rôle : Gère le footer des pages.
 * Auteur : Lucas LEPAPE, Gustin MAILHÉ
 */
?>

</div>

<footer class="bg-dark text-white py-4 mt-auto border-top rounded-top-5">
  <div class="container text-center">

    <!-- Liens du footer -->
    <ul class="nav justify-content-center border-bottom border-secondary pb-3 mb-3">
      <li class="nav-item"><a href="/" class="nav-link px-2 text-white">Accueil</a></li>
      <li class="nav-item"><a href="/features" class="nav-link px-2 text-white">Connexion</a></li>
      <li class="nav-item"><a href="/pricing" class="nav-link px-2 text-white">Inscription</a></li>
      <li class="nav-item"><a href="/faqs" class="nav-link px-2 text-white">Mentions Légales</a></li>
      <li class="nav-item"><a href="/about" class="nav-link px-2 text-white">Plan du site</a></li>
    </ul>

    <!-- Copyright -->
    <p class="text-center text-white-50 mb-0 small">
      © <?= date('Y'); ?> EnergyDash — Tous droits réservés
    </p>

  </div>
</footer>

<script src="<?= BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
