<?php
/**
 * Fichier : profile.php
 * Rôle : Vue du profil utilisateur standard.
 *
 * @var array{first_name: string, last_name: string, email: string} $user Informations de l'utilisateur
 */
?>
<?php if (!empty($_SESSION['success']) && is_string($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php elseif (!empty($_SESSION['error']) && is_string($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="fixed left-0 top-0 -z-10 h-full w-full">
    <div class="absolute inset-0 -z-10 h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#444445_1px,transparent_1px)]  [background-size:16px_16px]"></div>
</div>
<main id="content" class="w-full max-w-2xl mx-auto p-6 py-20">
        <div class="mt-7 bg-white border border-gray-200 rounded-xl shadow-2xs dark:bg-neutral-900 dark:border-neutral-700">
            <div class="p-4 mx-4 sm:p-7 ">
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 dark:text-neutral-200">
                    Profil
                </h2>
                <p class="text-sm text-gray-600 dark:text-neutral-400">
                    Modifiez les informations de votre compte.
                </p>
            </div>

            <form method="POST" action="/profile/update">
                <div class="grid sm:grid-cols-12 gap-2 sm:gap-6">

                    <div class="sm:col-span-3">
                        <label for="af-account-full-name" class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
                            Nom complet
                        </label>
                    </div>

                    <div class="sm:col-span-9">
                        <div class="sm:flex">
                            <input id="first_name" name="first_name" type="text" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs -mt-px -ms-px first:rounded-t-lg last:rounded-b-lg sm:first:rounded-s-lg sm:mt-0 sm:first:ms-0 sm:first:rounded-se-none sm:last:rounded-es-none sm:last:rounded-e-lg sm:text-sm relative focus:z-10 focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="John" value="<?= htmlspecialchars($user['first_name']); ?>">
                            <input id="last_name" name="last_name" type="text" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs -mt-px -ms-px first:rounded-t-lg last:rounded-b-lg sm:first:rounded-s-lg sm:mt-0 sm:first:ms-0 sm:first:rounded-se-none sm:last:rounded-es-none sm:last:rounded-e-lg sm:text-sm relative focus:z-10 focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Doe" value="<?= htmlspecialchars($user['last_name']); ?>">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="af-account-email" class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
                            Email
                        </label>
                    </div>

                    <div class="sm:col-span-9">
                        <input name="email" id="email" type="email" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs sm:text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="john.doe@gmail.com" value="<?= htmlspecialchars($user['email']); ?>">
                    </div>

                    <div class="sm:col-span-3">
                        <label for="af-account-password" class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
                            Mot de passe
                        </label>
                    </div>

                    <div class="sm:col-span-9">
                        <div class="space-y-2">
                            <input id="af-account-password" type="password" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Entrez votre mot de passe actuel">
                            <input id="password" name="password" type="password" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Entrez votre nouveau mot de passe">
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-x-2">
                    <button type="reset" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                        Annuler
                    </button>
                    <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
        </div>
</main>