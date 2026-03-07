<?php
/**
 * Fichier : forgot.php
 * Rôle : Vue du formulaire de demande de réinitialisation de mot de passe (oubli de mot de passe).
 */
?>

<?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 text-sm text-red-800 rounded-lg p-4 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500 mx-2"
        role="alert" tabindex="-1" aria-labelledby="hs-with-list-label">
        <div class="flex">
            <div class="shrink-0">
                <svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="m15 9-6 6"></path>
                    <path d="m9 9 6 6"></path>
                </svg>
            </div>
            <div class="ms-4">
                <h3 id="hs-with-list-label" class="text-sm font-semibold">
                    Un problème s'est produit lors de l'envoi de vos données.
                </h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                    <ul class="list-disc space-y-1 ps-5">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 text-sm text-green-800 rounded-lg p-4 dark:bg-green-800/10 dark:border-green-900 dark:text-green-500 mx-2 mb-4"
        role="alert">
        <div class="flex">
            <div class="shrink-0">
                <svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="ms-4">
                <p><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="fixed left-0 top-0 -z-10 h-full w-full">
    <div
        class="absolute inset-0 -z-10 h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#444445_1px,transparent_1px)]  [background-size:16px_16px]">
    </div>
</div>

<main id="content" class="w-full max-w-md mx-auto p-6">
    <div
        class="my-50 bg-white border border-gray-200 rounded-xl shadow-2xs dark:bg-neutral-900 dark:border-neutral-700">
        <div class="p-4 sm:p-7">
            <div class="text-center">
                <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">Mot de passe oublié ?</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
                    Vous vous souvenez de votre mot de passe ?
                    <a class="text-blue-600 decoration-2 hover:underline focus:outline-hidden focus:underline font-medium dark:text-blue-500"
                        href="/login">
                        Connectez-vous ici
                    </a>
                </p>
            </div>

            <div class="mt-5">
                <form method="post" action="/forgot">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="grid gap-y-4">
                        <div>
                            <label for="email" class="block text-sm mb-2 dark:text-white">Adresse-mail</label>
                            <div class="relative">
                                <input type="email" id="email" name="email" placeholder="john.doe@gmail.com"
                                    class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                    required aria-describedby="email-error">
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">Réinitialiser</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="/assets/js/auth.js"></script>