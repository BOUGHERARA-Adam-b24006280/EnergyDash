<!doctype html>
<html lang="fr" data-bs-theme="<?= $_COOKIE['theme'] ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EnergyDash - votre tableau de bord intelligent pour gérer vos données énergétiques.">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Shared.css">
    <link rel="stylesheet" href="assets/css/VarColors.css">
    <script href="assets/js/Toggle.js"></script>
</head>
<body>
<!-- Header -->
<header>
    <?php include "views/shared/Header.php"; ?>
</header>

<!-- Contenu principal (variable selon la page) affiche une page d'erreur si la page n'est pas trouvé-->
<main>
    <?php
        if (isset($body)) {
            include $body;
        }
        else {
            include "views/home/SiteMap.xsl.php";
            //include "views/error/Error404.php";
        }
    ?>
</main>

<!-- Footer -->
<footer>
    <?php include "views/shared/Footer.php"; ?>
</footer>
</body>
</html>