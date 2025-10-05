<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Accueil') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/css/bootstrap.min.css">
    <style>
        body { background-color: #F8F4F1; }
        .btn-primary { background-color: #9945E3 !important; border-color: #9945E3 !important; }
        .btn-outline-primary { color: #9945E3 !important; border: 1px solid #9945E3 !important; }
        .btn-outline-primary:hover { background-color: #9945E3 !important; color: white !important; }
        .btn-primary:hover { background-color: #9945E3 !important; }
        button { background-color: #7749f8 !important; border: 0 !important; }
        .card { background-color: #F8F4F1; }
        .purple { color: #9945E3 !important; }
    </style>
</head>
<body class="container-fluid">

    <header class="col">
        <?php include __DIR__ . '/../shared/navbar.php'; ?>
    </header>

    <main class="d-flex justify-content-center align-items-start" style="min-height:70vh;">
        <div class="text-center">
            <h1 class="display-1 fw-bold lh-1">Bienvenue dans<br>votre tableau de bord</h1>
        </div>
    </main>

    <script src="<?= BASE_URL ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

</html>
