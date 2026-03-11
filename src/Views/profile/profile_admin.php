<?php
/**
 * Fichier : profile_admin.php
 * Rôle : Vue du profil administrateur (gestion du compte et des utilisateurs).
 *
 * @var array{id: int, first_name: string, last_name: string, email: string, role: string} $user L'utilisateur connecté
 * @var array<int, array{id: int, first_name: string, last_name: string, email: string, role: string}> $users Liste complète des utilisateurs
 */

    $rawToken = $csrf_token ?? $_SESSION['csrf_token'] ?? '';
    $safeCsrf = is_string($rawToken) ? $rawToken : '';
?>

<div class="fixed left-0 top-0 -z-10 h-full w-full">
    <div class="absolute inset-0 -z-10 h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#444445_1px,transparent_1px)]  [background-size:16px_16px]"></div>
</div>

<main>
    <div id="content" class="w-full max-w-2xl mx-auto p-6 py-20">
        <div class="mt-7 bg-white border border-gray-200 rounded-xl shadow-2xs dark:bg-neutral-900 dark:border-neutral-700">
            <div class="p-4 mx-4 sm:p-7 ">
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-neutral-200">
                        Profile
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                        Modifiez les informations de votre compte.
                    </p>
                </div>

                <form method="POST" action="/profile/update">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($safeCsrf, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="grid sm:grid-cols-12 gap-2 sm:gap-6">

                        <div class="sm:col-span-3">
                            <label for="af-account-full-name" class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
                                Nom complet
                            </label>
                        </div>

                        <div class="sm:col-span-9">
                            <div class="sm:flex">
                                <input id="first_name" name="first_name" type="text" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs -mt-px -ms-px first:rounded-t-lg last:rounded-b-lg sm:first:rounded-s-lg sm:mt-0 sm:first:ms-0 sm:first:rounded-se-none sm:last:rounded-es-none sm:last:rounded-e-lg sm:text-sm relative focus:z-10 focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="John" value="<?= htmlspecialchars(
                                        $user["first_name"]
                                ) ?>">
                                <input id="last_name" name="last_name" type="text" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs -mt-px -ms-px first:rounded-t-lg last:rounded-b-lg sm:first:rounded-s-lg sm:mt-0 sm:first:ms-0 sm:first:rounded-se-none sm:last:rounded-es-none sm:last:rounded-e-lg sm:text-sm relative focus:z-10 focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Doe" value="<?= htmlspecialchars(
                                        $user["last_name"]
                                ) ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="af-account-email" class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
                                Email
                            </label>
                        </div>

                        <div class="sm:col-span-9">
                            <input name="email" id="email" type="email" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs sm:text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="john.doe@gmail.com" value="<?= htmlspecialchars(
                                    $user["email"]
                            ) ?>">
                        </div>

                        <div class="sm:col-span-3">
                            <label for="af-account-password" class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
                                Mot de passe
                            </label>
                        </div>

                        <div class="sm:col-span-9">
                            <div class="space-y-2">
                                <input id="af-account-password" type="text" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Entrez votre mot de passe actuel">
                                <input id="password" type="text" class="py-1.5 sm:py-2 px-3 pe-11 block w-full border-gray-200 shadow-2xs rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Entrez votre nouveau mot de passe">
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
    </div>

    <div class="max-w-[85rem] px-4 sm:px-6 lg:px-8 mx-auto mb-8">
        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs dark:bg-neutral-900 dark:border-neutral-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                    Ajouter un utilisateur
                </h2>
                <p class="text-sm text-gray-600 dark:text-neutral-400">
                    Créez un nouveau compte manuellement. Son rôle par défaut sera "Utilisateur".
                </p>
            </div>
            
            <div class="p-4 sm:p-6">
                <form method="POST" action="/profile/createUser">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($safeCsrf, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="grid sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="new_first_name" class="block text-sm font-medium mb-2 dark:text-white">Prénom</label>
                            <input type="text" id="new_first_name" name="first_name" required class="py-2.5 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                        </div>
                        <div>
                            <label for="new_last_name" class="block text-sm font-medium mb-2 dark:text-white">Nom</label>
                            <input type="text" id="new_last_name" name="last_name" required class="py-2.5 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                        </div>
                        <div>
                            <label for="new_email" class="block text-sm font-medium mb-2 dark:text-white">Email</label>
                            <input type="email" id="new_email" name="email" required class="py-2.5 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                        </div>
                        <div>
                            <label for="new_password" class="block text-sm font-medium mb-2 dark:text-white">Mot de passe temporaire</label>
                            <input type="text" id="new_password" name="password" required class="py-2.5 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-x-2">
                        <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700">
                            Créer l'utilisateur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--- Table Users Admin --->
    <div class="max-w-[85rem] px-4 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        <div class="flex flex-col">
            <div class="-m-1.5">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <div class="bg-white border border-gray-200 rounded-xl shadow-2xs dark:bg-neutral-900 dark:border-neutral-700">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                                Droits
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-neutral-400">
                                Configurez les droits des utilisateurs de la plateforme.
                            </p>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">Nom</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">Prénom</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">Email</th>
                                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">Rôle</th>
                                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">Action</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">

                            <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-gray-100 dark:hover:bg-neutral-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200"><?= htmlspecialchars(
                                                $u["last_name"]
                                        ) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200"><?= htmlspecialchars(
                                                $u["first_name"]
                                        ) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200"><?= htmlspecialchars(
                                                $u["email"]
                                        ) ?></td>
                                    <td class="py-4">
                                        <form method="POST" action="/profile/updateRole" class="d-flex justify-content-center" id="edit-<?= $u['id'] ?>">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($safeCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                            <select name="role" data-hs-select='{
                                                            "placeholder": "Choisir une option...",
                                                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                                            "toggleClasses": "hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 relative py-3 ps-4 pe-9 flex gap-x-2 text-nowrap w-full cursor-pointer bg-white border border-gray-200 rounded-lg text-start text-sm focus:outline-hidden focus:ring-2 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:focus:outline-hidden dark:focus:ring-1 dark:focus:ring-neutral-600",
                                                            "dropdownClasses": "mt-2 z-50 w-full max-h-72 p-1 space-y-0.5 bg-white border border-gray-200 rounded-lg overflow-hidden overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500 dark:bg-neutral-900 dark:border-neutral-700",
                                                            "optionClasses": "py-2 px-4 w-full text-sm text-gray-800 cursor-pointer hover:bg-gray-100 rounded-lg focus:outline-hidden focus:bg-gray-100 hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 dark:bg-neutral-900 dark:hover:bg-neutral-800 dark:text-neutral-200 dark:focus:bg-neutral-800",
                                                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-3.5 text-blue-600 dark:text-blue-500 \" xmlns=\"http:.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><polyline points=\"20 6 9 17 4 12\"/></svg></span></div>",
                                                            "extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 \" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
                                                            }' class="hidden">
                                                <?php if ($u["role"] === "admin"): ?>
                                                    <option value="admin" selected>Administrateur</option>
                                                <?php else: ?>
                                                    <option value="user" <?= $u[
                                                    "role"
                                                    ] === "user"
                                                            ? "selected"
                                                            : "" ?>>Utilisateur</option>
                                                    <option value="editor" <?= $u[
                                                    "role"
                                                    ] === "editor"
                                                            ? "selected"
                                                            : "" ?>>Autorisé (Import)</option>
                                                <?php endif; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium flex justify-end items-center gap-x-3">
                                        <button form="edit-<?= $u['id'] ?>" type="submit" class="inline-flex items-center gap-x-1 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 focus:outline-hidden focus:text-blue-800 disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400 dark:focus:text-blue-400">Modifier</button>
                                        
                                        <form method="POST" action="/profile/deleteUser" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cet utilisateur ? Cette action est irréversible.');" class="inline-block m-0 p-0">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($safeCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="inline-flex items-center gap-x-1 text-sm font-semibold rounded-lg border border-transparent text-red-600 hover:text-red-800 focus:outline-hidden focus:text-red-800 disabled:opacity-50 disabled:pointer-events-none dark:text-red-500 dark:hover:text-red-400 dark:focus:text-red-400">
                                                Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--- End Table Users Admin --->
</main>
