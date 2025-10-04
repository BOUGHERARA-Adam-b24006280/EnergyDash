<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Accueil') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="d-flex flex-column min-vh-100">

<?php
// définir la couleur utilisée
$violet = '#701CBA';
?>

<nav class="navbar" style="background:transparent;">
  <div class="container-fluid px-3 px-md-4 position-relative">

    <!-- gauche (brand) -->
    <div class="d-inline-flex align-items-center" style="min-width:140px;">
      <a class="navbar-brand mb-0" href="<?= BASE_URL ?>/">EnergyDash</a>
    </div>

    <!-- centre (toujours centré) -->
    <div class="position-absolute start-50 translate-middle-x">
      <a class="nav-link px-3" href="<?= BASE_URL ?>/">Home</a>
    </div>

    <!-- droite (boutons) -->
    <div class="d-inline-flex align-items-center ms-auto" style="min-width:160px; justify-content:flex-end;">
      <!-- Outline -->
      <a class="btn btn-sm"
         href="<?= BASE_URL ?>/login"
         style="background: transparent; border: 1.5px solid <?= $violet ?>; color: <?= $violet ?>; border-radius:8px;">
         Se connecter
      </a>

      <!-- Filled -->
      <a class="btn btn-sm ms-2"
         href="<?= BASE_URL ?>/register"
         style="background: <?= $violet ?>; border: 1px solid <?= $violet ?>; color: #fff; border-radius:8px;">
         S'inscrire
      </a>
    </div>

  </div>
</nav>

<main class="flex-fill">
  <div class="container py-5">
    <h1>Page d'accueil</h1>
    <p class="text-muted">Bienvenue sur EnergyDash !</p>
  </div>
</main>

<footer class="py-2 mt-auto mx-3 mx-md-4 mb-2" style="background:#701CBA; border-radius:12px;">
  <div class="container-fluid px-2 px-md-3">
    <nav aria-label="Footer" class="mb-2">
      <ul class="nav justify-content-center border-bottom pb-2 mb-2">
        <li class="nav-item"><a class="nav-link text-white px-2" href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="nav-item"><a class="nav-link text-white px-2" href="<?= BASE_URL ?>/login">Connexion</a></li>
        <li class="nav-item"><a class="nav-link text-white px-2" href="<?= BASE_URL ?>/register">Inscription</a></li>
        <li class="nav-item"><a class="nav-link text-white px-2" href="<?= BASE_URL ?>/dashboard">Dashboard</a></li>
      </ul>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
      <p class="mb-0 small text-white">© <?= date('Y') ?> EnergyDash — Tous droits réservés</p>
    </div>
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
