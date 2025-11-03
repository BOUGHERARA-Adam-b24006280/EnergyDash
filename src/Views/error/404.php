<?php
/**
 * Fichier : 404.php
 * Rôle : Affiche une page d'erreur 404 personnalisée
 * Auteur : Adam BOUGHERARA, Lucas LEPAPE
 */
?>

<div class="fixed left-0 top-0 -z-10 h-full w-full">
    <div class="absolute inset-0 -z-10 h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#444445_1px,transparent_1px)]  [background-size:16px_16px]"></div>
</div>

<main id="content">
    <div class="text-center py-10 px-4 sm:px-6 lg:px-8">
        <img src="/assets/images/Lucas.png" class="mt-20 mx-auto size-40 grayscale-100 w-auto h-auto">
        <h1 class="block text-7xl font-bold text-gray-800 sm:text-9xl dark:text-white">404</h1>
        <p class="mt-3 text-gray-600 dark:text-neutral-400">Oups, quelque chose s'est mal passé.</p>
        <p class="text-gray-600 dark:text-neutral-400">Désolé, nous n'avons pas trouvé votre page.</p>
        <div class="mt-5 flex flex-col justify-center items-center gap-2 sm:flex-row sm:gap-3">
            <a class="w-full sm:w-auto py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent mb-50 bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none" href="/">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Revenir à l’accueil
            </a>
        </div>
    </div>
</main>
