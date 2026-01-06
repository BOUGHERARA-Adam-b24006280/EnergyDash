<?php
/**
 * Fichier : dashboard.php
 * Rôle : Vue principale du tableau de bord.
 * Contient :
 * 1. Formulaire d'upload CSV (avec bouton Submit).
 * 2. Formulaire de filtres (Type, Ville, Date).
 * 3. Graphique Chart.js interactif.
 *
 * Auteur : L'équipe EnergyDash
 */
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

    <?php if ($msg = $this->getFlash('success')): ?>
        <div class="bg-green-100 border border-green-200 text-green-800 rounded-lg p-4 mb-6 dark:bg-green-800/10 dark:border-green-900 dark:text-green-500">
            <strong>Succès :</strong> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($msg = $this->getFlash('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500">
            <strong>Erreur :</strong> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>


    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <div class="lg:col-span-4 space-y-6">
            
        <?php 
        $userRole = $_SESSION['user']['role'] ?? 'user';
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
                            <input type="date" name="from" value="2023-12-01" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-white">Au</label>
                            <input type="date" name="to" value="2023-12-31" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-400">
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
    
    // Éléments du DOM pour l'interactivité
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

    // --- NOUVEAU : Fonction qui gère les règles d'interface ---
    function handleInterfaceRules() {
        const isComparing = compareSelect.value !== "";
        
        // Règle : Si on compare une ville, on désactive Météo et Température
        secondaryRadios.forEach(radio => {
            if (radio.value !== 'none') {
                // On désactive le bouton
                radio.disabled = isComparing;
                
                // Visuellement, on grise le parent pour bien montrer que c'est inactif
                const label = radio.parentElement;
                if (isComparing) {
                    label.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    label.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        });

        // Si on est en mode comparaison, on force la sélection sur "Aucune"
        if (isComparing) {
            noneRadio.checked = true;
        }
    }

    // On écoute le changement sur le menu "Comparer"
    compareSelect.addEventListener('change', handleInterfaceRules);

    // ---------------------------------------------------------

    async function refreshChart() {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        const type = formData.get('type');
        const city = formData.get('city');

        // On applique les règles d'interface juste avant d'envoyer (au cas où)
        handleInterfaceRules();

        const url = `http://localhost:8000/api/energy?${params}`;

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
        // ON ENLÈVE LA RESTRICTION ICI : On veut voir la météo même en mode "Total"
        // const isAllTypes = (type === 'all'); 
        const theme = themes[type] || themes['all'];

        let dates = [];
        let dataCity1 = [], dataSecondary = [], dataCity2 = [];

        rawData.forEach(item => {
            if (!dates.includes(item.date)) dates.push(item.date);
        });
        dates.sort();

        dates.forEach(date => {
            // Ville 1
            const points1 = rawData.filter(d => d.date === date && d.ville.toLowerCase() === mainCity.toLowerCase());
            const totalProd1 = points1.reduce((sum, p) => sum + parseFloat(p.production), 0);
            dataCity1.push(totalProd1);

            // --- CORRECTION RÉCUPÉRATION DONNÉES SECONDAIRES ---
            let secValue = 0;
            if (points1.length > 0) {
                if (secondaryMode === 'meteo') {
                    // ASTUCE : Si on a plusieurs énergies (Solaire + Hydraulique), l'une peut avoir météo=0.
                    // On prend la valeur MAXIMALE trouvée pour cette heure-là pour être sûr d'afficher quelque chose.
                    const maxMeteo = points1.reduce((max, p) => Math.max(max, parseFloat(p.meteo || 0)), 0);
                    secValue = maxMeteo;
                } 
                else if (secondaryMode === 'temp') {
                    // Pour la température, on prend la première valeur valide trouvée
                    const pWithTemp = points1.find(p => p.temp !== 0); 
                    secValue = pWithTemp ? parseFloat(pWithTemp.temp) : 0;
                }
            }
            dataSecondary.push(secValue);

            // Ville 2
            if (compareCity) {
                const points2 = rawData.filter(d => d.date === date && d.ville.toLowerCase() === compareCity.toLowerCase());
                const total2 = points2.reduce((sum, p) => sum + parseFloat(p.production), 0);
                dataCity2.push(total2);
            }
        });

        // --- Dataset 1 : Ville Principale ---
        let datasets = [{
            label: `Production ${mainCity}`,
            data: dataCity1,
            borderColor: theme.color,
            backgroundColor: theme.bg,
            yAxisID: 'y',
            tension: 0.3,
            fill: true
        }];

        // --- Dataset 2 : Météo ou Température ---
        // CORRECTION : On affiche la météo PEU IMPORTE le type (on a enlevé !isAllTypes)
        if (secondaryMode === 'meteo') {
            datasets.push({
                label: 'Indice Météo',
                data: dataSecondary,
                borderColor: '#fbbf24', // Jaune
                borderDash: [5, 5],     // Pointillés
                pointRadius: 0,
                yAxisID: 'y1',
                tension: 0.1,
                fill: false
            });
        } else if (secondaryMode === 'temp') {
            datasets.push({
                label: 'Température (°C)',
                data: dataSecondary,
                borderColor: '#ef4444', // Rouge
                borderWidth: 2,
                borderDash: [5, 5],     // Pointillés
                pointRadius: 0,
                yAxisID: 'y1',
                tension: 0.4,
                fill: false
            });
        }

        // --- Dataset 3 : Comparaison ---
        if (compareCity && compareCity !== "") {
            datasets.push({
                label: `Production ${compareCity}`,
                data: dataCity2,
                borderColor: '#9ca3af', // Gris
                borderWidth: 2,
                yAxisID: 'y',
                tension: 0.3,
                fill: false
            });
        }

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels: dates, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { 
                        type: 'linear', position: 'left', beginAtZero: true,
                        title: { display: true, text: 'Production (kW)' }
                    },
                    y1: {
                        type: 'linear', position: 'right',
                        // On affiche l'axe si un mode secondaire est actif (plus de restriction 'all')
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

    // Appel initial
    handleInterfaceRules();
    refreshChart();
});
</script>