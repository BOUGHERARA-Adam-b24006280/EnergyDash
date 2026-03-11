<?php
/**
 * Fichier : dashboard.php
 * Rôle : Vue principale du tableau de bord.
 * @var array<int, string> $cities 
 * @var array<string, array<int, string>> $energyMapping 
 * @var mixed $success 
 * @var mixed $error 
 * @var mixed $csrf_token
 */

    $user = $_SESSION['user'] ?? null;
    $userId = 0;

    if (is_array($user) && isset($user['id']) && is_numeric($user['id'])) $userId = (int) $user['id'];

    $userFilePath = __DIR__ . '/../../../Storage/energy_user_' . $userId . '.csv';
    $fileExists = file_exists($userFilePath);

    $tokenFromSession = $_SESSION['csrf_token'] ?? '';
    $rawToken = $csrf_token ?? $tokenFromSession;
    $safeCsrf = is_string($rawToken) ? $rawToken : '';
?>

<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
    
    <div class="mb-8 border-b border-gray-200 dark:border-gray-700 pb-5">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
            Tableau de bord énergétique
        </h2>
        <p class="text-sm text-gray-600 dark:text-neutral-400 mt-1">
            Gérez vos données de production via l'import CSV et visualisez les statistiques.
        </p>
    </div>

    <?php if (isset($success) && is_string($success) && !empty($success)): ?>
        <div class="bg-green-100 border border-green-200 text-green-800 rounded-lg p-4 mb-6 dark:bg-green-800/10 dark:border-green-900 dark:text-green-500">
            <strong>Succès :</strong> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error) && is_string($error) && !empty($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500">
            <strong>Erreur :</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>


    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <div class="lg:col-span-4 space-y-6">
            
        <?php 
        $userRole = (is_array($user) && isset($user['role']) && is_string($user['role'])) ? $user['role'] : 'user';
        ?>

        <?php if (in_array($userRole, ['admin', 'editor'])): ?>

            <div class="bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-700 p-5">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">
                    Importer un fichier
                </h3>
                <p class="text-xs text-gray-500 mb-4">
                    Format requis : <strong>.csv</strong><br>
                    Colonnes : <code>type, ville, date_heure, production_kw, valeur_meteo</code>
                </p>

                <form action="/energy/upload" method="POST" enctype="multipart/form-data" class="space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($safeCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <label class="block">
                        <span class="sr-only">Choisir un fichier</span>
                        <input type="file" name="csv_file" accept=".csv" required
                            class="block w-full text-sm text-gray-500
                            file:me-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-600 file:text-white
                            hover:file:bg-blue-700
                            file:disabled:opacity-50 file:disabled:pointer-events-none
                            dark:file:bg-blue-500 dark:hover:file:bg-blue-400
                            border border-gray-200 rounded-lg shadow-sm">
                    </label>

                    <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        CLIQUER ICI POUR ENVOYER
                    </button>
                </form>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-neutral-700">
                        <div class="flex items-center justify-between">
                            
                            <div>
                                <?php if ($fileExists): ?>
                                    <span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-500">
                                        <svg class="flex-shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                        Fichier actif
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php 
                                            $mtime = filemtime($userFilePath);
                                            $dateStr = ($mtime !== false) ? date("d/m/Y H:i", $mtime) : 'Inconnue';
                                        ?>
                                        Modifié le : <?= htmlspecialchars($dateStr) ?>
                                    </p>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white">
                                        Aucun fichier perso
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if ($fileExists): ?>
                                <form action="/energy/delete" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer vos données et revenir à l\'affichage par défaut ?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($safeCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-red-600 hover:bg-red-100 disabled:opacity-50 disabled:pointer-events-none dark:text-red-500 dark:hover:bg-red-800/30">
                                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        Supprimer
                                    </button>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>

            </div>
        
        <?php endif; ?>

            <div class="bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-700 p-5">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">
                    Filtres d'affichage
                </h3>
                
                <form id="energyForm" class="space-y-4">
                    
                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-white">Type d'énergie</label>
                        <select name="type" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-400">
                            <option value="all">Toutes les énergies (Total)</option>
                            <option value="solaire">Solaire</option>
                            <option value="eolien">Éolien</option>
                            <option value="hydraulique">Hydraulique</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-white">Ville Principale</label>
                        <select name="city" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-400">
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-white">Comparer avec (Optionnel)</label>
                        <select name="compare" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-400">
                            <option value="">-- Aucune --</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-white">Du</label>
                            <input type="date" name="from" value="2026-01-01" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-white">Au</label>
                            <input type="date" name="to" value="2026-01-08" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-400">
                        </div>
                    </div>

                    <div class="mt-4">
                        <span class="block text-sm font-medium mb-2 dark:text-white">Donnée secondaire (Axe de droite) :</span>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="secondaryView" value="none" checked class="form-radio text-gray-600 border-gray-300 focus:ring-gray-500">
                                <span class="ml-2 text-sm dark:text-gray-300">Aucune</span>
                            </label>

                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="secondaryView" value="meteo" class="form-radio text-yellow-500 border-gray-300 focus:ring-yellow-500">
                                <span class="ml-2 text-sm dark:text-gray-300">Indice Météo</span>
                            </label>

                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="secondaryView" value="temp" class="form-radio text-red-600 border-gray-300 focus:ring-red-500">
                                <span class="ml-2 text-sm dark:text-gray-300">Température</span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full py-2 px-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold transition-colors">
                        Mettre à jour le graphique
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8 space-y-6">
            <?php 
            $userRole = (is_array($user) && isset($user['role'])) ? $user['role'] : 'user';
            $isAdmin = in_array($userRole, ['admin']);
            
            $algoFile = __DIR__ . '/../../../Storage/active_algo.txt';
            $activeAlgo = 'standard';

            if (file_exists($algoFile)) {
                $content = file_get_contents($algoFile);
                if (is_string($content)) {
                    $activeAlgo = trim($content);
                }
            }
            ?>

            <?php if ($isAdmin): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 dark:bg-blue-900/20 dark:border-blue-800">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                        <h3 class="text-md font-bold text-blue-800 dark:text-blue-300">Panneau de contrôle IA (Administrateur)</h3>
                        
                        <label id="backtestContainer" class="inline-flex items-center cursor-pointer bg-white dark:bg-neutral-800 px-4 py-2 rounded-lg border border-gray-200 shadow-sm transition-colors duration-300">
                            <input type="checkbox" id="backtestToggle" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            <span id="backtestLabel" class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Mode Backtest : <strong>INACTIF</strong></span>
                        </label>
                    </div>
                    
                    <form action="/energy/setAlgorithm" method="POST" class="flex flex-col sm:flex-row items-center gap-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($safeCsrf, ENT_QUOTES, 'UTF-8') ?>">
                        <select name="algo" class="py-2 px-3 border-gray-200 rounded-lg text-sm w-full sm:w-auto dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-400">
                            <option value="standard" <?= $activeAlgo === 'standard' ? 'selected' : '' ?>>Algorithme Standard (Météo)</option>
                            <option value="lstm" <?= $activeAlgo === 'lstm' ? 'selected' : '' ?>>Algorithme LSTM (Réseau de neurones)</option>
                        </select>
                        <button type="submit" class="w-full sm:w-auto py-2 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Appliquer pour tous les clients
                        </button>
                    </form>
                </div>

                <div class="bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-neutral-900 p-5 flex flex-col" style="height: 450px;">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white" id="chartTitleStd">Modèle 1 : Standard (Météo)</h3>
                        <span id="errorStd" class="hidden px-3 py-1 bg-gray-100 text-gray-800 font-bold rounded-lg text-sm dark:bg-neutral-800 dark:text-white border border-gray-200"></span>
                    </div>
                    <div class="relative flex-1 w-full"><canvas id="energyChartStandard"></canvas></div>
                </div>

                <div class="bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-neutral-900 p-5 flex flex-col mt-6" style="height: 450px;">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white" id="chartTitleLstm">Modèle 2 : LSTM (Deep Learning)</h3>
                        <span id="errorLstm" class="hidden px-3 py-1 bg-red-100 text-red-800 font-bold rounded-lg text-sm dark:bg-red-900/30 dark:text-red-400 border border-red-200"></span>
                    </div>
                    <div class="relative flex-1 w-full"><canvas id="energyChartLSTM"></canvas></div>
                </div>

            <?php else: ?>
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-neutral-900 p-5 flex flex-col" style="height: 600px;">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white" id="chartTitle">
                            Graphique de production
                        </h3>
                        <span class="text-xs font-medium px-2 py-1 bg-blue-100 text-blue-800 rounded dark:bg-blue-900 dark:text-blue-300">
                            Propulsé par IA (<?= strtoupper($activeAlgo) ?>)
                        </span>
                    </div>
                    <div class="relative flex-1 w-full">
                        <canvas id="energyChart"></canvas>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    window.energyMappingData = <?= json_encode($energyMapping) ?>;
</script>

<script src="/assets/js/dashboard.js"></script>