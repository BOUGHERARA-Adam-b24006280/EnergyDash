<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Accueil') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/css/bootstrap.min.css">
    <style>
        body { background-color: #F8F4F1; }
        button { background-color: #7749f8 !important; border: 0 !important; }
        .card { background-color: #F8F4F1; }
        .purple { color: #9945E3 !important; }
    </style>
</head>
<body class="container-fluid">
    <?php include __DIR__ . '/../shared/navbar.php'; ?>
    <h1>Page d'accueil</h1>
    <p class="text-muted">Bienvenue sur EnergyDash !</p>
  </div>
</main>

<script src="<?= BASE_URL ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
