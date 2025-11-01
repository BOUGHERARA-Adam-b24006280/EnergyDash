<?php
/**
 * Fichier : profile.php
 * Rôle : 
 * Auteur : Lucas LEPAPE,
 */
?>

<div class="container mt-5 pt-5">
    <h1 class="fw-bold mb-4">Mon profil</h1>

    <div class="card shadow-sm p-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-2 text-center">
                <i class="bi bi-person-circle fs-1 text-primary"></i>
            </div>
            <div class="col-md-10">
                <p class="mb-2"><strong>Prénom :</strong> <?= htmlspecialchars($user['first_name']); ?></p>
                <p class="mb-2"><strong>Nom :</strong> <?= htmlspecialchars($user['last_name']); ?></p>
                <p class="mb-2"><strong>Email :</strong> <?= htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <hr>

        <div class="text-end">
            <a href="/dashboard" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
            <a href="/logout" class="btn btn-danger">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a>
        </div>
    </div>
</div>
