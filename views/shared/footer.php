<footer class="py-2 mt-auto mx-3 mx-md-4 mb-2" style="background:#701CBA; border-radius:12px;">
    <div class="container-fluid px-2 px-md-3">
        <nav aria-label="Footer" class="mb-2">
            <ul class="nav justify-content-center border-bottom pb-2 mb-2">
                <li class="nav-item">
                <a class="nav-link text-white px-2" href="<?= BASE_URL ?>/">Accueil</a>
                </li>
                <li class="nav-item">
                <a class="nav-link text-white px-2" href="<?= BASE_URL ?>/login">Connexion</a>
                </li>
                <li class="nav-item">
                <a class="nav-link text-white px-2" href="<?= BASE_URL ?>/register">Inscription</a>
                </li>
                <li class="nav-item">
                <a class="nav-link text-white px-2" href="<?= BASE_URL ?>/dashboard">Dashboard</a>
                </li>
            </ul>
        </nav>

        <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
            <p class="mb-0 small text-white">© <?= date('Y') ?> EnergyDash — Tous droits réservés</p>
        </div>
    </div>
</footer>