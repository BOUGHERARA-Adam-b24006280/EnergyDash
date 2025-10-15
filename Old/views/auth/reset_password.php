<main class="container py-5">
    <h1 class="text-center mb-4">Réinitialiser votre mot de passe</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <label for="password" class="form-label">Nouveau mot de passe</label>
        <input type="password" name="password" id="password" class="form-control mb-3" required>

        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control mb-4" required>

        <button type="submit" class="btn btn-primary w-100">Enregistrer le nouveau mot de passe</button>
    </form>
</main>
