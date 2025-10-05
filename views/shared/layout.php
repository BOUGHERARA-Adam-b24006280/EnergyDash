<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-with, inital-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/css/bootstrap.min.css">
    <title><?= htmlspecialchars($title ?? 'EnergyDash') ?></title>
    <style>
        body { background-color: #F8F4F1; }
        button { background-color: #7749f8 !important; border: 0 !important; }
        .card { background-color: #F8F4F1; }
        .a { color: #701CBA !important; }
    </style>
</head>
<?php
include $navbar;
include $body;
include $footer;
?>
</html>