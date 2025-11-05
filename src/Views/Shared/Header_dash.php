<?php
/**
 * Fichier : Header.php
 * Rôle : Gère le header des pages
 * Auteur :  Adam BOUGHERARA, Lucas LEPAPE, Gustin MAILHÉ
 */

/** @var string $title */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupère les infos utilisateur si connecté
$user = $_SESSION['user'] ?? null;

// Crée une variable avec prénom + nom si connecté
$fullName = $user ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) : null;
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
    <nav class="relative max-w-340 w-full mx-auto md:flex md:items-center md:justify-between md:gap-3 py-2 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center gap-x-1">
            <a class="flex-none font-bold text-3xl text-blue-600 focus:outline-hidden focus:opacity-80 dark:text-white" href="/" aria-label="EnergyDash">Energy Dash</a>

            <button type="button" class="hs-collapse-toggle md:hidden relative size-9 flex justify-center items-center font-medium text-sm rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-white dark:border-neutral-700 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" id="hs-header-base-collapse" aria-expanded="false" aria-controls="hs-header-base" aria-label="Toggle navigation" data-hs-collapse="#hs-header-base">
                <svg class="hs-collapse-open:hidden size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" x2="21" y1="6" y2="6" />
                    <line x1="3" x2="21" y1="12" y2="12" />
                    <line x1="3" x2="21" y1="18" y2="18" />
                </svg>
                <svg class="hs-collapse-open:block shrink-0 hidden size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
                <span class="sr-only">Toggle navigation</span>
            </button>
        </div>

        <div id="hs-header-base" class="hs-collapse hidden overflow-visible transition-all duration-300 basis-full grow md:block" aria-labelledby="hs-header-base-collapse">
            <div class="py-2 md:py-0 flex flex-col md:flex-row md:items-center gap-0.5 md:gap-1">
                <div class="grow"></div>

                <div class="flex flex-wrap items-center gap-x-1.5">
                    <?php if (!$user): ?>
                        <!-- Si personne n'est connecté -->
                        <a class="py-[7px] px-2.5 inline-flex items-center font-medium text-sm rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 dark:bg-neutral-800 focus:outline-hidden focus:bg-gray-100 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" href="/login">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" class="lucide lucide-user">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span class="ps-2">Connexion</span>
                        </a>
                        <a class="py-2 px-2.5 inline-flex items-center font-medium text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:bg-blue-600" href="/register">
                            Inscription
                        </a>

                    <?php else: ?>
                        <!-- Si un utilisateur est connecté -->
                        <div class="relative inline-block text-left">
                        <button id="menuButton"
                            onclick="toggleMenu()"
                            class="py-[7px] px-2.5 inline-flex items-center font-medium text-sm rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-100 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700">
                            <?= $fullName; ?>
                        </button>

                            <!-- Menu déroulant animé -->
                            <div id="dropdownMenu"
                                class="hidden absolute right-0 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-neutral-800 dark:ring-neutral-700 transform scale-95 opacity-0 transition-all duration-200">
                                <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700">
                                    Profil
                                </a>
                                <a href="/dashboard" class="block px-4 py-2 text-sm text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700">
                                    Tableau de bord
                                </a>
                                <a href="/logout" class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-neutral-700">
                                    Déconnexion
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const menu = document.getElementById('dropdownMenu');
    const button = document.getElementById('menuButton');

    function toggleMenu() {
        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            menu.classList.remove('hidden');
            setTimeout(() => {
                menu.classList.remove('opacity-0', 'scale-95');
                menu.classList.add('opacity-100', 'scale-100');
            }, 10);
        } else {
            menu.classList.add('opacity-0', 'scale-95');
            menu.classList.remove('opacity-100', 'scale-100');
            setTimeout(() => menu.classList.add('hidden'), 150);
        }
    }

    // Écouteur sur le bouton
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMenu();
    });

    // Ferme le menu si clic ailleurs
    document.addEventListener('click', (e) => {
        if (!button.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('opacity-0', 'scale-95');
            menu.classList.remove('opacity-100', 'scale-100');
            setTimeout(() => menu.classList.add('hidden'), 150);
        }
    });
});
</script>

<body class="dark:bg-neutral-900">