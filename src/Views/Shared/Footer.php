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
                <h4 class="font-semibold text-gray-100">Energy Dash</h4>

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
        </div>

        <div class="mt-5 sm:mt-12 grid gap-y-2 sm:gap-y-0 sm:flex sm:justify-between sm:items-center">
            <div class="flex flex-wrap justify-between items-center gap-2">
                <p class="text-sm text-gray-400 dark:text-neutral-400">
                    © 2025 EnergyDash. Tout droits réservés.
                </p>
            </div>
        </div>
    </div>
</footer>
<!-- ========== END FOOTER ========== -->

<script src="/assets/preline/dist/preline.js"></script>
</body>
</html>
