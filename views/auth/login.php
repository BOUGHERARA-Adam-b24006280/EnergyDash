<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <title><?php echo $title; ?></title>

    <style>
        body {
            background-color: #F8F4F1;
        }

        button {
            background-color: #7749f8 !important;
            border: 0 !important;
        }

        .card {
            background-color: #F8F4F1;
        }

        .a {
            color: #701CBA !important;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-10 col-xl-5 col-sm-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="POST">
                            <h2 class="text-center fw-bold p-3">Connexion</h1>
                                <div class="ps-5 pe-5 mt-3">
                                    <label for="email" class="form-label">Adresse mail *</label>
                                    <input type="email" class="form-control" id="email" placeholder="john.doe@gmail.com"
                                        required>
                                </div>

                                <div class="ps-5 pe-5 mt-3">
                                    <label for="password" class="form-label">Mot de passe *</label>
                                    <input type="password" id="password" class="form-control"
                                        aria-describedby="passwordHelpBlock" placeholder="Mot de passe" required>
                                </div>

                                <div class="ps-5 pe-5 pt-4">
                                    <button type="submit" class="btn btn-primary w-100 p-3 fw-semibold">
                                        Se connecter
                                    </button>
                                </div>
                        </form>
                    </div>
                    <div class="card-footer text-center p-3">
                        <span class="fw-regular text-secondary">Pas de comptes ? </span> <a
                            class="fw-bold link-underline link-underline-opacity-0" href="/register">S'inscrire</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>