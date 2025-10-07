<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EnergyDash - votre tableau de bord intelligent pour gérer vos données énergétiques.">
    <title><?= htmlspecialchars($title ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <h1>Tableau de bord</h1>
    <p class="text-success">Bienvenue, vous êtes connecté !</p>
    <a class="btn btn-danger" href="<?= BASE_URL ?>/logout">Déconnexion</a>

    <script src="<?= BASE_URL ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
