<?php
/**
 * Fichier : dashboard.php
 * Rôle : Vue principale du tableau de bord.
 * 
 * @var array<int, string> $cities Liste des villes disponibles
 * @var string|null $success Message de succès
 * @var string|null $error Message d'erreur
 */

    $user = $_SESSION['user'] ?? null;
    $userId = 0;

    if (is_array($user) && isset($user['id']) && is_numeric($user['id'])) {
        $userId = (int) $user['id'];
    }

    $userFilePath = __DIR__ . '/../../../Storage/energy_user_' . $userId . '.csv';
    $fileExists = file_exists($userFilePath);
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

    <?php if (!empty($success)): ?>
        <div class="bg-green-100 border border-green-200 text-green-800 rounded-lg p-4 mb-6 dark:bg-green-800/10 dark:border-green-900 dark:text-green-500">
            <strong>Succès :</strong> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500">
            <strong>Erreur :</strong> <?= htmlspecialchars($error) ?>
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

        <div class="lg:col-span-8">
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-700 p-5 h-full min-h-[500px] flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white" id="chartTitle">
                        Graphique de production
                    </h3>
                    <span class="text-xs text-gray-400">Données en kW</span>
                </div>
                
                <div class="relative flex-1 w-full">
                    <canvas id="energyChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('energyForm');
    const ctx = document.getElementById('energyChart').getContext('2d');
    const titleEl = document.getElementById('chartTitle');
    
    const compareSelect = document.querySelector('select[name="compare"]');
    const secondaryRadios = document.querySelectorAll('input[name="secondaryView"]');
    const noneRadio = document.querySelector('input[value="none"]');

    let chartInstance = null;

    const themes = {
        all:         { color: '#8b5cf6', bg: 'rgba(139, 92, 246, 0.2)' },
        solaire:     { color: '#f59e0b', bg: 'rgba(245, 158, 11, 0.2)' },
        eolien:      { color: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' },
        hydraulique: { color: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' }
    };

    function handleInterfaceRules() {
        const isComparing = compareSelect.value !== "";
        
        secondaryRadios.forEach(radio => {
            if (radio.value !== 'none') {
                radio.disabled = isComparing;
                
                const label = radio.parentElement;
                if (isComparing) {
                    label.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    label.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        });

        if (isComparing) {
            noneRadio.checked = true;
        }
    }

    compareSelect.addEventListener('change', handleInterfaceRules);


    async function refreshChart() {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        const type = formData.get('type');
        const city = formData.get('city');

        handleInterfaceRules();

        const url = `/api/energy?${params}`;

        try {
            const req = await fetch(url);
            if(!req.ok) throw new Error("Erreur serveur");
            const json = await req.json();

            updateChart(json, type);
            
            let typeLabel = (type === 'all') ? 'Production Totale' : type.charAt(0).toUpperCase() + type.slice(1);
            let cityText = (city === 'all') ? 'Toutes zones' : city;
            titleEl.textContent = `${typeLabel} (${cityText})`;

        } catch (e) {
            console.error(e);
            titleEl.textContent = "Erreur de chargement";
        }
    }

    function updateChart(jsonData, type) {
        if (chartInstance) chartInstance.destroy();

        const rawData = jsonData.data;
        const mainCity = jsonData.city;
        const formData = new FormData(document.getElementById('energyForm'));
        const compareCity = formData.get('compare');
        
        const secondaryMode = document.querySelector('input[name="secondaryView"]:checked').value;
        const theme = themes[type] || themes['all'];

        const isPrediction = rawData.length > 0 && rawData[0].statut === 'prevision';

        let dates = [];
        let dataCity1 = [], dataSecondary = [], dataCity2 = [];

        rawData.forEach(item => {
            if (!dates.includes(item.date)) dates.push(item.date);
        });
        dates.sort();

        dates.forEach(date => {
            const points1 = rawData.filter(d => d.date === date && d.ville.toLowerCase() === mainCity.toLowerCase());
            const totalProd1 = points1.reduce((sum, p) => sum + parseFloat(p.production), 0);
            dataCity1.push(totalProd1);

            let secValue = 0;
            if (points1.length > 0) {
                if (secondaryMode === 'meteo') {
                    const maxMeteo = points1.reduce((max, p) => Math.max(max, parseFloat(p.meteo || 0)), 0);
                    secValue = maxMeteo;
                } 
                else if (secondaryMode === 'temp') {
                    const pWithTemp = points1.find(p => p.temp !== 0 && p.temp !== null); 
                    secValue = pWithTemp ? parseFloat(pWithTemp.temp) : 0;
                }
            }
            dataSecondary.push(secValue);

            if (compareCity) {
                const points2 = rawData.filter(d => d.date === date && d.ville.toLowerCase() === compareCity.toLowerCase());
                const total2 = points2.reduce((sum, p) => sum + parseFloat(p.production), 0);
                dataCity2.push(total2);
            }
        });
    
        let datasets = [{
            label: isPrediction ? `Prévision ${mainCity} (IA)` : `Production ${mainCity}`,
            data: dataCity1,
            borderColor: theme.color,
            backgroundColor: theme.bg,
            yAxisID: 'y',
            tension: 0.4,
            fill: true,
            
            borderDash: isPrediction ? [10, 5] : [],
            pointRadius: isPrediction ? 0 : 3,
            pointHoverRadius: 6
        }];

        if (secondaryMode === 'meteo') {
            datasets.push({
                label: 'Indice Météo (Vent/Soleil/Pluie)',
                data: dataSecondary,
                borderColor: '#fbbf24',
                borderDash: [5, 5],
                pointRadius: 0,
                yAxisID: 'y1',
                tension: 0.1,
                fill: false
            });
        } else if (secondaryMode === 'temp') {
            datasets.push({
                label: 'Température (°C)',
                data: dataSecondary,
                borderColor: '#ef4444',
                borderWidth: 2,
                borderDash: [5, 5],
                pointRadius: 0,
                yAxisID: 'y1',
                tension: 0.4,
                fill: false
            });
        }

        if (compareCity && compareCity !== "") {
            datasets.push({
                label: `Production ${compareCity}`,
                data: dataCity2,
                borderColor: '#9ca3af',
                borderWidth: 2,
                yAxisID: 'y',
                tension: 0.3,
                fill: false,
                borderDash: isPrediction ? [5, 5] : [] 
            });
        }

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels: dates, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y;
                                    if (context.dataset.yAxisID === 'y') label += ' kW';
                                    else if (secondaryMode === 'temp') label += ' °C';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        type: 'linear', position: 'left', beginAtZero: true,
                        title: { display: true, text: 'Production (kW)' }
                    },
                    y1: {
                        type: 'linear', position: 'right',
                        display: (secondaryMode !== 'none'),
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: secondaryMode === 'temp' ? 'Température (°C)' : 'Indice Météo' }
                    }
                }
            }
        });
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        refreshChart();
    });

    handleInterfaceRules();
    refreshChart();
});
</script>