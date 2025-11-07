<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
</head>
<body>
    <h2>Mot de passe oublié</h2>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" action="/forgot">
        <label for="email">Adresse e-mail :</label><br>
        <input type="email" name="email" id="email" required>
        <button type="submit">Envoyer le lien</button>
    </form>
</body>
</html>