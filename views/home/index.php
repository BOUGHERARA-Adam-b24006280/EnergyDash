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
$violet = '#9945E3';
?>

<main class="flex-fill">
  <div class="container py-5">
    <h1>Page d'accueil</h1>
    <p class="text-muted">Bienvenue sur EnergyDash !</p>
  </div>
</main>

<script src="<?= BASE_URL ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
