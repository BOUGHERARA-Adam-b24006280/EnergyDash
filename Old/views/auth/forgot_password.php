<main class="container py-5">
    <h1 class="text-center mb-4">Mot de passe oublié</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <label for="email" class="form-label">Adresse e-mail</label>
        <input type="email" name="email" id="email" class="form-control mb-3" required>
        <button type="submit" class="btn btn-primary w-100">Réinitialiser le mot de passe</button>
    </form>
</main>
