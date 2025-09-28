<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Accueil') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <h1>Page d'accueil</h1>
    <p class="text-muted">Bienvenue sur EnergyDash !</p>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/login">Connexion</a>
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/register">Inscription</a>

    <script src="<?= BASE_URL ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
