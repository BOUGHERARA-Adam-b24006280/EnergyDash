<?php
/**
 * Fichier : index.php
 * Rôle : Vue de la page d'accueil.
 */
?>

<main id="content">
    <div class="relative overflow-hidden before:absolute before:top-0 before:start-1/2 before:bg-[url('https://preline.co/assets/svg/examples/squared-bg-element.svg')] dark:before:bg-[url('https://preline.co/assets/svg/examples-dark/squared-bg-element.svg')] before:bg-no-repeat before:bg-top before:size-full before:-z-1 before:transform before:-translate-x-1/2">
        <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-10 min-h-screen">

            <div class="mt-5 max-w-xl text-center mx-auto">
                <h1 class="block font-bold text-gray-800 text-4xl md:text-5xl lg:text-6xl dark:text-neutral-200">
                    Visualisez, analysez, optimisez.
                </h1>
            </div>

            <div class="mt-5 max-w-3xl text-center mx-auto">
                <p class="text-lg text-gray-600 dark:text-neutral-400">Analysez les performances grâce à des KPIs clairs, identifiez les tendances et comparez-les aux prévisions</p>
            </div>

            <div class="mt-8 gap-3 flex justify-center">
                <a class="inline-flex justify-center items-center gap-x-3 text-center bg-linear-to-tl from-blue-600 to-violet-600 hover:from-violet-600 hover:to-blue-600 focus:outline-hidden focus:from-violet-600 focus:to-blue-600 border border-transparent text-white text-sm font-medium rounded-full py-3 px-4" href="/dashboard">
                    Commencer
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevrons-right-icon lucide-chevrons-right"><path d="m6 17 5-5-5-5"/><path d="m13 17 5-5-5-5"/></svg>
                </a>
            </div>
        </div>
    </div>
</main>