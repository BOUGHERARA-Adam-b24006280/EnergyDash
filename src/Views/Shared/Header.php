<?php
/**
 * Fichier : Header.php
 * Rôle : Gère le header des pages
 * Auteur :  Adam BOUGHERARA, Lucas LEPAPE, Gustin MAILHÉ
 */

/** @var string $title */
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

    <!-- Couleurs personnalisées -->
    <link rel="stylesheet" href="/assets/css/VarColors.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand bg-transparent position-absolute w-100 top-0 start-0">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-dark" href="/">EnergyDash</a>

        <!-- Boutons -->
        <div class="d-flex align-items-center">
            <!-- Visible sur mobile uniquement -->
            <a class="btn btn-primary fw-bold d-inline-block d-lg-none" href="/login">Se connecter</a>

            <!-- Visible sur PC uniquement -->
            <a class="btn btn-outline-primary pe-3 ps-3 fw-bold me-3 d-none d-lg-inline-block" href="/login">Se connecter</a>
            <a class="btn btn-primary pe-4 ps-4 fw-bold d-none d-lg-inline-block" href="/register">S'inscrire</a>
        </div>
    </div>
</nav>

<div class="container py-4">