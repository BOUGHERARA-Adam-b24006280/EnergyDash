<a href="/">EnergyDash</a>

<button onclick="<?= \controllers\HomeController::class->switchTheme() ?>">
du texte
</button>

<div class="d-flex align-items-center">
    <a class="btn btn-outline-primary pe-3 ps-3 fw-bold me-3" href="/login">Se connecter</a>
    <a class="btn btn-primary pe-4 ps-4 fw-bold" href="/register">S'inscrire</a>
</div>