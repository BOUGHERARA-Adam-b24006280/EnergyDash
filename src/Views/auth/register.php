<?php
/**
 * Fichier : register.php
 * Rôle : View de la page d'inscription.
 * Auteur : Lucas LEPAPE, Adam Bougherara
 *
 * @var array $errors
 * @var string $csrf_token
 */
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger position-absolute container-fluid top-0 start-50 translate-middle-x z-1" role="alert">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="container mt-5 mt-md-4 mt-lg-2 px-4 px-md-3 px-lg-1">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-10 col-xl-5 col-sm-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>" method="post">
                        <h2 class="text-center fw-bold p-3">Inscription</h2>
                        <div class="ps-5 pe-5 mt-3">
                            <label for="first_name" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" placeholder="John" required>
                        </div>
                        <div class="ps-5 pe-5 mt-3">
                            <label for="last_name" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" placeholder="Doe" required>
                        </div>
                        <div class="ps-5 pe-5 mt-3">
                            <label for="email" class="form-label">Adresse mail *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="john.doe@gmail.com" required>
                        </div>
                        <div class="ps-5 pe-5 mt-3">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" id="password" class="form-control" name="password"
                                   placeholder="Mot de passe" required minlength="8">
                        </div>
                        <div class="ps-5 pe-5 mt-3">
                            <label for="confirm_password">Confirmer le mot de passe *</label>
                            <input type="password" id="confirm_password" class="form-control" name="confirm_password"
                                   required minlength="8">
                            <div class="form-text">Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule, un chiffre et un symbole.</div>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="ps-5 pe-5 mt-3 mb-4">
                            <button type="submit" class="btn btn-primary w-100 p-3 fw-semibold form-button">
                                S'inscrire
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center p-3">
                    <span class="fw-regular text-secondary">Déjà inscrit ? </span>
                    <a class="fw-bold link-underline link-underline-opacity-0 purple" href="/login">Se connecter</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelector('form').addEventListener('submit', function() {
    const btn = document.querySelector('.form-button');
    btn.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
    btn.disabled = true;
});
</script>
