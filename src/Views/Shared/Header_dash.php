<?php
/**
 * Fichier : Header.php
 * Rôle : Gère le header des pages
 * Auteur : Adam BOUGHERARA, Lucas LEPAPE, Gustin MAILHÉ
 */

/** @var string $title */
/** @var array|null $user — Contient les infos de l'utilisateur connecté */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title); ?></title>
    <meta name="description" content="EnergyDash - votre tableau de bord intelligent pour gérer vos données énergétiques.">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon/favicon-16x16.png">
    <link rel="shortcut icon" href="/assets/images/favicon/favicon.ico" type="image/x-icon">

    <!-- Bootstrap local -->
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">

    <!-- Icônes Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Couleurs personnalisées -->
    <link rel="stylesheet" href="/assets/css/VarColors.css">
</head>

<body class="bg-light d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand bg-transparent position-absolute w-100 top-0 start-0">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-dark" href="/">EnergyDash</a>

        <!-- Zone profil -->
        <?php if ($user): ?>
            <div class="dropdown">
                <!-- Bouton profil toujours visible -->
                <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" 
                        type="button" 
                        id="userMenu" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false">
                    <i class="bi bi-person-circle me-2"></i>
                    <?= htmlspecialchars($user['first_name'] ?? ''); ?>
                    <?= htmlspecialchars($user['last_name'] ?? ''); ?>
                </button>

                <!-- Menu déroulant -->
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                    <li><a class="dropdown-item" href="/profile">Mon profil</a></li>
                    <li><a class="dropdown-item" href="/dashboard">Tableau de bord</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="/logout" method="post" class="m-0">
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-1"></i> Se déconnecter
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        <?php else: ?>
            <div class="d-flex align-items-center">
                <a class="btn btn-outline-primary me-3 fw-bold" href="/login">Se connecter</a>
                <a class="btn btn-primary fw-bold" href="/register">S'inscrire</a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<div class="container py-4">
