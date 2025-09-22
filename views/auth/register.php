<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
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
    </style>
</head>

<body>
      <?php if (isset($error)): ?>
        <div class="alert alert-danger position-absolute container-fluid top-0 start-50 translate-middle-x z-1" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="container">

        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-10 col-xl-5 col-sm-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="register" method="post">
                            <h2 class="text-center fw-bold p-3">Inscription</h1>
                                <div class="ps-5 pe-5 mt-3">
                                    <label for="first_name" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="John">
                                </div>
                                <div class="ps-5 pe-5 mt-3">
                                    <label for="last_name" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Doe">
                                </div>

                                <div class="ps-5 pe-5 mt-3">
                                    <label for="email" class="form-label">Adresse mail *</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="john.doe@gmail.com"
                                    >
                                </div>

                                <div class="ps-5 pe-5 mt-3">
                                    <label for="password" class="form-label">Mot de passe *</label>
                                    <input type="password" id="password" class="form-control" name="password"
                                        aria-describedby="passwordHelpBlock" placeholder="Mot de passe">
                                    <div class="form-text"> Le mot de passe doit contenir au moins bcp de caractère
                                    </div>
                                </div>

                                <div class="ps-5 pe-5 mt-3 mb-4">
                                    <button type="submit" class="btn btn-primary w-100 p-3 fw-semibold form-button" >
                                        S'inscrire
                                    </button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Afficher un spinner lors de la soumission du formulaire
    document.querySelector('form').addEventListener('submit', function(e) {
        const btn = document.querySelector('.form-button');
        btn.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
        btn.disabled = true;
    });

    </script>
    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>