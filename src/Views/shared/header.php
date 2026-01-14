<?php
/**
 * Fichier : header.php
 * Rôle : Gère le header des pages
 */

/** @var string $title */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupère les infos utilisateur si connecté
$user = $_SESSION['user'] ?? null;
$fullName = null;

// Correction PHPStan : On vérifie que $user est un tableau
if (is_array($user)) {
    // On récupère et valide le prénom
    $firstName = $user['first_name'] ?? 'Utilisateur';
    if (!is_string($firstName)) {
        $firstName = 'Utilisateur';
    }

    // On récupère et valide le nom
    $lastName = $user['last_name'] ?? '';
    if (!is_string($lastName)) {
        $lastName = '';
    }

    // Concaténation sécurisée maintenant que tout est typé string
    $fullName = htmlspecialchars($firstName . ' ' . $lastName);
}
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
    <link rel="stylesheet" href="/assets/tailwindcss/style.css">
</head>

<body>
<header class="flex flex-wrap md:justify-start md:flex-nowrap z-50 w-full">
    <nav class="relative max-w-340 w-full mx-auto flex items-center justify-between md:gap-3 py-2 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-x-1">
            <a class="flex-none font-bold text-3xl text-blue-600 focus:outline-hidden focus:opacity-80 dark:text-white" 
               href="/" aria-label="EnergyDash">
                Energy Dash
            </a>
        </div>

        <!-- Zone de droite : boutons ou menu utilisateur -->
        <div class="flex flex-wrap items-center gap-x-1.5">
            <?php if (!$user): ?>
                <!-- Si personne n'est connecté -->
                <a class="py-[7px] px-2.5 inline-flex items-center font-medium text-sm rounded-lg border border-gray-200 
                    bg-white text-gray-800 shadow-2xs hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none 
                    dark:bg-neutral-800 focus:outline-hidden focus:bg-gray-100 dark:border-neutral-700 dark:text-neutral-300 
                    dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" href="/login">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" 
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                         class="lucide lucide-user-icon lucide-user">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span class="ps-2">Connexion</span>
                </a>

                <a id="registerButton" class="py-2 px-2.5 inline-flex items-center font-medium text-sm rounded-lg bg-blue-600 text-white 
                    hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 
                    disabled:pointer-events-none dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:bg-blue-600" 
                    href="/register">
                    Inscription
                </a>
            <?php else: ?>
                <!-- Si un utilisateur est connecté -->
                <div class="hs-dropdown relative inline-flex [--placement:top-right]">
                    <button id="hs-dropdown-with-icons" type="button" class="hs-dropdown-toggle py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        <?= $fullName ?>
                        <svg class="hs-dropdown-open:rotate-180 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg mt-2 divide-y divide-gray-200 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700 z-50" role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-with-icons">
                        <div class="p-1 space-y-0.5">
                            <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700" href="/profile">
                                <svg class="shrink-0 mt-0.5 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Profil
                            </a>
                            <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700" href="/dashboard">
                                <svg class="shrink-0 mt-0.5 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16v5"/><path d="M16 14v7"/><path d="M20 10v11"/><path d="m22 3-8.646 8.646a.5.5 0 0 1-.708 0L9.354 8.354a.5.5 0 0 0-.707 0L2 15"/><path d="M4 18v3"/><path d="M8 14v7"/></svg> Tableau de bord
                            </a>
                            <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700" href="/logout">
                                <svg class="shrink-0 mt-0.5 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/></svg>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<body class="dark:bg-neutral-900">