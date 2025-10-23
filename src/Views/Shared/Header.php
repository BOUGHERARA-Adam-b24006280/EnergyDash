<?php
/**
 * Fichier : Header.php
 * Rôle : Gère le header des pages
 * Auteur : Lucas LEPAPE, Gustin MAILHÉ
 */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($this->getTitle()); ?></title>

    <!-- Bootstrap local -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/bootstrap/css/bootstrap.min.css">

    <!-- Ton style perso -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL; ?>/">⚡ EnergyDash</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL; ?>/">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL; ?>/login">Connexion</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL; ?>/register">Inscription</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL; ?>/dashboard">Dashboard</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">