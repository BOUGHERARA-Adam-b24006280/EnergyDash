<?php
/**
 * Fichier : reset.php
 * Rôle : View pour la réinitialisation du mot de passe.
 *
 * @var array<int, string> $errors Liste des messages d'erreur
 * @var string $success Messages de succès
 * @var string $csrf_token Jeton CSRF
 */

$uri = $_SERVER['REQUEST_URI'] ?? '';
?>

<?php if (!empty($errors)): ?>
<div class="bg-red-50 border border-red-200 text-sm text-red-800 rounded-lg p-4 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500 mx-2" role="alert">
    <div class="flex">
        <div class="shrink-0">
            <svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="m15 9-6 6"></path>
                <path d="m9 9 6 6"></path>
            </svg>
        </div>
        <div class="ms-4">
            <h3 class="text-sm font-semibold">Une erreur est survenue</h3>
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
<div class="bg-green-50 border border-green-200 text-sm text-green-800 rounded-lg p-4 dark:bg-green-800/10 dark:border-green-900 dark:text-green-500 mx-2" role="alert">
    <div class="flex">
        <div class="shrink-0">
             <svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <div class="ms-4">
            <h3 class="text-sm font-semibold">Succès !</h3>
            <div class="mt-2 text-sm text-green-700 dark:text-green-400">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                <div class="mt-3">
                    <a href="/login" class="inline-flex gap-x-2 text-sm font-semibold underline decoration-2 hover:text-green-800 dark:hover:text-green-400">
                        Retour à la connexion
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($success)): ?>
<div class="fixed left-0 top-0 -z-10 h-full w-full">
    <div class="absolute inset-0 -z-10 h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#444445_1px,transparent_1px)]  [background-size:16px_16px]"></div>
</div>

<main id="content" class="w-full max-w-md mx-auto p-6 py-20">
    <div class="mt-7 bg-white border border-gray-200 rounded-xl shadow-2xs dark:bg-neutral-900 dark:border-neutral-700">
        <div class="p-4 sm:p-7">
            <div class="text-center">
                <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">Réinitialisation</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
                    Veuillez choisir un nouveau mot de passe sécurisé.
                </p>
            </div>

            <div class="mt-5">
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

                    <?php 
                        $token = $_GET['token'] ?? '';
                        if (!is_string($token)) {
                            $token = '';
                        }
                    ?>

                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="grid gap-y-4">
                        <!-- Password Group -->
                        <div>
                            <label for="password" class="block text-sm mb-2 dark:text-white">Nouveau mot de passe</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" required>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-neutral-500">8 caractères, majuscule, minuscule, chiffre, symbole.</p>
                        </div>
                        
                        <!-- Confirm Group -->
                        <div>
                            <label for="confirm_password" class="block text-sm mb-2 dark:text-white">Confirmer le mot de passe</label>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="confirm_password" class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" required>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                            Réinitialiser le mot de passe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php endif; ?>