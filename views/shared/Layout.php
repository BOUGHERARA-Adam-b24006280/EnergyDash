<?php
    $theme = "light";
?>

<!doctype html>
<html lang="fr" data-bs-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EnergyDash - votre tableau de bord intelligent pour gérer vos données énergétiques.">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Shared.css">
    <script href="assets/js/Toggle.js"></script>
</head>
<body>
<!-- Header -->
<header>
    <?php include "views/shared/Header.php"; ?>
</header>

<!-- Contenu principal (variable selon la page) -->
<main>
    <?php include "views/home/BodyTeste.php"; ?>
</main>

<!-- Footer -->
<footer>
    <?php include "views/shared/Footer.php"; ?>
</footer>
</body>
</html>