<?php
/**
 * Fichier : profile.php
 * Rôle : 
 * Auteur : Lucas LEPAPE,
 */
?>

<div class="container mt-5 pt-5">
    <h1 class="fw-bold mb-4 text-center">Information</h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php elseif (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="/profile/update" class="card shadow-sm p-4 mx-auto" style="max-width: 600px;">
        <div class="mb-3">
            <label for="first_name" class="form-label">Prénom :</label>
            <input type="text" name="first_name" id="first_name" class="form-control"
                   value="<?= htmlspecialchars($user['first_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">Nom :</label>
            <input type="text" name="last_name" id="last_name" class="form-control"
                   value="<?= htmlspecialchars($user['last_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse mail :</label>
            <input type="email" name="email" id="email" class="form-control"
                   value="<?= htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nouveau mot de passe :</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Laisser vide pour ne pas changer">
        </div>

        <div class="text-end">
            <button type="reset" class="btn btn-outline-secondary me-2">Réinitialiser</button>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>
