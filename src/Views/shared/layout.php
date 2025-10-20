<!DOCTYPE html>
<html lang="fr" data-bs-theme="<?= $_COOKIE['theme'] ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'EnergyDash') ?></title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Shared.css">
    <link rel="stylesheet" href="assets/css/VarColors.css">
</head>
<body>

    <?php include __DIR__ . '/header.php'; ?>

    <main>
        <?= $content ?? '' ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>