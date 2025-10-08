<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EnergyDash - votre tableau de bord intelligent pour gérer vos données énergétiques.">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/assets/images/favicon/favicon-16x16.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/css/bootstrap.min.css">
    <title><?= htmlspecialchars($title ?? 'Connexion') ?></title>
    <style>
        body { background-color: #F8F4F1; }
        button { background-color: #7749f8 !important; border: 0 !important; }
        .card { background-color: #F8F4F1; }
        .purple { color: #9945E3 !important; }
        
    </style>
</head>
<body>
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-success position-absolute container-fluid top-0 start-50 translate-middle-x z-1 text-center" role="alert">
            <?= htmlspecialchars($_SESSION['flash']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <section>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger position-absolute container-fluid top-0 start-50 translate-middle-x z-1" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-10 col-xl-5 col-sm-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="<?= BASE_URL ?>/login" method="post">
                            <h2 class="text-center fw-bold p-3">Connexion</h2>

                            <div class="ps-5 pe-5 mt-3">
                                <label for="email" class="form-label">Adresse mail *</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="john.doe@gmail.com">
                            </div>

                            <div class="ps-5 pe-5 mt-3">
                                <label for="password" class="form-label">Mot de passe *</label>
                                <input type="password" id="password" class="form-control" name="password" placeholder="Mot de passe">
                            </div>

                            <div class="ps-5 pe-5 pt-4">
                                <button type="submit" class="btn btn-primary w-100 p-3 fw-semibold form-button">
                                    Se connecter
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="<?= BASE_URL ?>/forgot-password" class="text-decoration-none purple fw-semibold">
                                    Mot de passe oublié ?
                                </a>
                            </div>

                        </form>
                    </div>
                    <div class="card-footer text-center p-3">
                        <span class="fw-regular text-secondary">Pas de compte ? </span>
                        <a class="fw-bold link-underline link-underline-opacity-0 purple" href="<?= BASE_URL ?>/register">S'inscrire</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.querySelector('.form-button');
        btn.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
        btn.disabled = true;
    });
    </script>
</body>
</html>
