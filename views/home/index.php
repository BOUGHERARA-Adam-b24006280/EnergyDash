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

    
    <footer class="py-3 my-4">
    <ul class="nav justify-content-center border-bottom pb-3 mb-3">
        <li class="nav-item">
            <a class="pe-3" href="<?= BASE_URL ?>/index" class="">Accueil</a>
        </li>
        <li class="nav-item">
            <a class="pe-3" href="<?= BASE_URL ?>/login" class="">Connexion</a>
        </li>
        <li class="nav-item">
            <a class="pe-3" href="<?= BASE_URL ?>/register" class="">Inscription</a>
        </li>
        <li class="nav-item">
            <a class="pe-3" href="<?= BASE_URL ?>/dashboard" class="">Dashboard</a>
        </li>
    </ul>
    <p class="text-center text-body-secondary">© 2025 Company, Inc</p>
</footer>

    <script src="<?= BASE_URL ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
