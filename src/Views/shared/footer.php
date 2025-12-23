<?php
/**
 * Fichier : Footer.php
 * Rôle : Gère le footer des pages.
 * Auteur : Lucas LEPAPE, Gustin MAILHÉ
 */
?>
<!-- ========== FOOTER ========== -->
<footer class="mt-auto bg-gray-900 w-full dark:bg-neutral-950">
    <div class="mt-auto w-full max-w-340 py-10 px-4 sm:px-6 lg:px-8 lg:pt-20 mx-auto">
        <!-- Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <div class="col-span-full lg:col-span-1">
                <a class="flex-none text-xl font-semibold text-white focus:outline-hidden focus:opacity-80" href="#" aria-label="EnergyDash">EnergyDash</a>
            </div>

            <div class="col-span-1">
                <h4 class="font-semibold text-gray-100">EnergyDash</h4>

                <div class="mt-3 grid space-y-3">
                    <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-gray-200 focus:outline-hidden focus:text-gray-200 dark:text-neutral-400 dark:hover:text-neutral-200 dark:focus:text-neutral-200" href="/dashboard">Dashboard</a></p>
                </div>
            </div>

            <div class="col-span-1">
                <h4 class="font-semibold text-gray-100">Compte</h4>

                <div class="mt-3 grid space-y-3">
                    <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-gray-200 focus:outline-hidden focus:text-gray-200 dark:text-neutral-400 dark:hover:text-neutral-200 dark:focus:text-neutral-200" href="/login">Connexion</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-gray-200 focus:outline-hidden focus:text-gray-200 dark:text-neutral-400 dark:hover:text-neutral-200 dark:focus:text-neutral-200" href="/register">Inscription</a></p>
                </div>
            </div>

            <div class="col-span-1">
                <h4 class="font-semibold text-gray-100">Juridique</h4>

                <div class="mt-3 grid space-y-3">
                    <p><a class="inline-flex gap-x-2 text-gray-400 hover:text-gray-200 focus:outline-hidden focus:text-gray-200 dark:text-neutral-400 dark:hover:text-neutral-200 dark:focus:text-neutral-200" href="/mentions">Mentions légales</a></p>
                </div>
            </div>
        </div>

        <div class="mt-5 sm:mt-12 grid gap-y-2 sm:gap-y-0 sm:flex sm:justify-between sm:items-center">
            <div class="flex flex-wrap justify-between items-center gap-2">
                <p class="text-sm text-gray-400 dark:text-neutral-400">
                    © 2025 Energy Dash. Tout droits réservés.
                </p>
            </div>
        </div>

        <div class="mt-5 sm:mt-12 grid gap-y-2 sm:gap-y-0 sm:flex sm:justify-between sm:items-center">
            <!-- Dark Mode -->
            <button type="button" class="hs-dark-mode hs-dark-mode-active:hidden relative flex justify-center items-center size-7 border border-gray-700 text-gray-500 rounded-full hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" data-hs-theme-click-value="dark">
                <span class="sr-only">Sombre</span>
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                </svg>
            </button>
            <button type="button" class="hs-dark-mode hs-dark-mode-active:flex hidden relative justify-center items-center size-7 border border-gray-200 text-gray-500 rounded-full hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" data-hs-theme-click-value="light">
                <span class="sr-only">Clair</span>
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2"></path>
                    <path d="M12 20v2"></path>
                    <path d="m4.93 4.93 1.41 1.41"></path>
                    <path d="m17.66 17.66 1.41 1.41"></path>
                    <path d="M2 12h2"></path>
                    <path d="M20 12h2"></path>
                    <path d="m6.34 17.66-1.41 1.41"></path>
                    <path d="m19.07 4.93-1.41 1.41"></path>
                </svg>
            </button>
            <!-- End Dark Mode -->
        </div>
    </div>
</footer>
<!-- ========== END FOOTER ========== -->

<script src="/assets/preline/dist/preline.js"></script>
</body>
</html>
