<h2>Réinitialiser le mot de passe</h2>

<?php if (!empty($errors)): ?>
    <div style="color:red"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div style="color:green"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="post">
    <label for="password">Nouveau mot de passe :</label><br>
    <input type="password" name="password" id="password" required><br>

    <label for="confirm_password">Confirmer le mot de passe :</label><br>
    <input type="password" name="confirm_password" id="confirm_password" required><br>

    <button type="submit">Réinitialiser</button>
</form>