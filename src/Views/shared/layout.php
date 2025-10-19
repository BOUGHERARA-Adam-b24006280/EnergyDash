<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'EnergyDash') ?></title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>

    <main class="container mt-4">
        <?= $content ?? '' ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>