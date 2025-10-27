<?php
/**
 * Fichier : login.php
 * Rôle : View de la page de connexion.
 * Auteur : Lucas LEPAPE, Adam Bougherara
 * 
 * @var array<int, string> $errors Liste des messages d'erreur
 * @var string $csrf_token Jetons CSRF unique côté serveur
 */

$uri = $_SERVER['REQUEST_URI'] ?? '';
$action = is_string($uri)
    ? htmlspecialchars($uri, ENT_QUOTES, 'UTF-8')
    : '';
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

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-10 col-xl-5 col-sm-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="<?= $action ?>" method="post">
                        <h2 class="text-center fw-bold p-3">Connexion</h2>

                        <div class="ps-5 pe-5 mt-3">
                            <label for="email" class="form-label">Adresse mail *</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="john.doe@gmail.com" required>
                        </div>

                        <div class="ps-5 pe-5 mt-3">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" id="password" class="form-control" name="password" placeholder="Mot de passe" required minlength="8">
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="ps-5 pe-5 pt-4">
                            <button type="submit" class="btn btn-primary w-100 p-3 fw-semibold form-button">
                                Se connecter
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="/forgot-password" class="text-decoration-none fw-semibold accent">
                                Mot de passe oublié ?
                            </a>
                        </div>

                    </form>
                </div>
                <div class="card-footer text-center p-3">
                    <span class="fw-regular text-secondary">Pas de compte ? </span>
                    <a class="fw-bold link-underline link-underline-opacity-0 purple" href="/register">S'inscrire</a>
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