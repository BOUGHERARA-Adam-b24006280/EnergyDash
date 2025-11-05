<div class="fixed left-0 top-0 -z-10 h-full w-full">
    <div class="absolute inset-0 -z-10 h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#444445_1px,transparent_1px)]  [background-size:16px_16px]"></div>
</div>

<main id="content" class="w-full max-w-md mx-auto p-6">
    <div class="my-50 bg-white border border-gray-200 rounded-xl shadow-2xs dark:bg-neutral-900 dark:border-neutral-700">
        <div class="p-4 sm:p-7">
            <div class="text-center">
                <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">Mot de passe oublié ?</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
                    Vous vous souvenez de votre mot de passe ?
                    <a class="text-blue-600 decoration-2 hover:underline focus:outline-hidden focus:underline font-medium dark:text-blue-500" href="/login">
                        Connectez-vous ici
                    </a>
                </p>
            </div>

            <div class="mt-5">
                <form>
                    <div class="grid gap-y-4">
                        <div>
                            <label for="email" class="block text-sm mb-2 dark:text-white">Adresse-mail</label>
                            <div class="relative">
                                <input type="email" id="email" name="email" placeholder="john.doe@gmail.com" class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" required aria-describedby="email-error">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">Réinitialiser</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>