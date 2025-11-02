<?php
/**
 * Fichier : login.php
 * Rôle : View de la page de connexion.
 * Auteur : Lucas LEPAPE, Adam Bougherara
 * 
 * @var array<int, string> $errors Liste des messages d'erreur
 * @var string $csrf_token Jetons CSRF unique côté serveur
 */

$uri = $_SERVER['REQUEST_URI'] ?? '';
$action = is_string($uri)
    ? htmlspecialchars($uri, ENT_QUOTES, 'UTF-8')
    : '';
?>

<?php if (!empty($errors)): ?>
<div class="bg-red-50 border border-red-200 text-sm text-red-800 rounded-lg p-4 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500 mx-2" role="alert" tabindex="-1" aria-labelledby="hs-with-list-label">
    <div class="flex">
        <div class="shrink-0">
            <svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

<div class="absolute inset-0 -z-10 h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px]"></div>

<main id="content" class="w-full max-w-md mx-auto p-6 py-20">
    <div class="mt-7 bg-white border border-gray-200 rounded-xl shadow-2xs dark:bg-neutral-900 dark:border-neutral-700">
        <div class="p-4 sm:p-7">
            <div class="text-center">
                <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">Connexion</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
                    Pas de compte ?
                    <a class="text-blue-600 decoration-2 hover:underline focus:outline-hidden focus:underline font-medium dark:text-blue-500" href="/register">
                        S'inscrire
                    </a>
                </p>
            </div>

            <div class="mt-5">
                <!-- Form -->
                <form action="<?= $action ?>" method="post">
                    <div class="grid gap-y-4">
                        <!-- Form Group -->
                        <div>
                            <label for="email" class="block text-sm mb-2 dark:text-white">Adresse mail</label>
                            <div class="relative">
                                <input type="email" id="email" placeholder="john.doe@gmail.com" name="email" class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" required aria-describedby="email-error">
                                <div class="hidden absolute inset-y-0 end-0 pointer-events-none pe-3">
                                    <svg class="size-5 text-red-500" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="hidden text-xs text-red-600 mt-2" id="email-error">Veuillez indiquer une adresse mail valide afin que nous puissions vous répondre.</p>
                        </div>
                        <!-- End Form Group -->

                        <!-- Form Group -->
                        <div>
                            <div class="flex flex-wrap justify-between items-center gap-2">
                                <label for="password" class="block text-sm mb-2 dark:text-white">Mot de passe</label>
                                <a class="inline-flex items-center gap-x-1 text-sm text-blue-600 decoration-2 hover:underline focus:outline-hidden focus:underline font-medium dark:text-blue-500" href="/recovery">Mot de passe oublié ?</a>
                            </div>
                            <div class="relative">
                                <input type="password" id="password" name="password" placeholder="Mot de passe" class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" required aria-describedby="password-error">
                            </div>
                            <p class="mt-2 mb-4 text-sm text-gray-500 dark:text-neutral-500" id="hs-input-helper-text">Votre mot de passe doit contenir au moins 8 caractères, une majuscule et une minuscule.</p>
                        </div>
                        <!-- End Form Group -->

                        <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">Connexion</button>
                    </div>
                </form>
                <!-- End Form -->
            </div>
        </div>
    </div>
</main>
<script>
document.querySelector('form').addEventListener('submit', function() {
    const btn = document.querySelector('.form-button');
    btn.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
    btn.disabled = true;
});
</script>