<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EnergyDash - votre tableau de bord intelligent pour gérer vos données énergétiques.">
    <title><?= htmlspecialchars($title ?? 'Accueil') ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/assets/images/favicon/favicon-16x16.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/css/bootstrap.min.css">
    <style>
        body { background-color: #F8F4F1; }
        .btn-primary { background-color: #7749f8 !important; border-color: #7749f8 !important; }
        .btn-outline-primary { color: #7749f8 !important; border: 1px solid #7749f8 !important; }
        .btn-outline-primary:hover { background-color: #7749f8 !important; color: white !important; }
        .btn-primary:hover { background-color: #7749f8 !important; }
        button { background-color: #7749f8 !important; border: 0 !important; }
        .card { background-color: #F8F4F1; }
        .purple { color: #9945E3 !important; }
    </style>
</head>
<body class="container-fluid">
<?php
include $navbar;
include $body;
include $footer;
?>
</body>
</html>