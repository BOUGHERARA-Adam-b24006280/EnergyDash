<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EnergyDash - votre tableau de bord intelligent pour gérer vos données énergétiques.">
    <title><?= htmlspecialchars($title ?? 'Dashboard') ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon/favicon-16x16.png">
    <link rel="shortcut icon" href="/assets/images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="d-flex flex-column">
    <h1>Tableau de bord</h1>
    <p class="text-success ">Bienvenue, vous êtes connecté !</p>
    <a class="btn btn-danger" href="/logout">Déconnexion</a>


    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
