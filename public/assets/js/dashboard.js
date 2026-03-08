document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('energyForm');
    const citySelect = document.querySelector('select[name="city"]');
    const typeSelect = document.querySelector('select[name="type"]');
    const compareSelect = document.querySelector('select[name="compare"]');
    const secondaryRadios = document.querySelectorAll('input[name="secondaryView"]');
    const noneRadio = document.querySelector('input[value="none"]');

    const energyMapping = window.energyMappingData || {};

    // Stockera tous les graphiques actifs pour pouvoir les mettre à jour
    let chartInstances = {}; 

    const themes = {
        all:         { color: '#8b5cf6', bg: 'rgba(139, 92, 246, 0.2)' },
        solaire:     { color: '#f59e0b', bg: 'rgba(245, 158, 11, 0.2)' },
        eolien:      { color: '#3b82f6', bg: 'rgba(59, 130, 246, 0.2)' },
        hydraulique: { color: '#10b981', bg: 'rgba(16, 185, 129, 0.2)' }
    };

    function updateTypeOptions() {
        const selectedCity = citySelect.value;
        const availableEnergies = (energyMapping[selectedCity] || []).map(e => e.toLowerCase());
        
        const typeOptions = typeSelect.querySelectorAll('option');
        let firstAvailable = null;

        typeOptions.forEach(option => {
            if (option.value === 'all') {
                option.hidden = (availableEnergies.length <= 1);
                return;
            }
            const isAvailable = availableEnergies.includes(option.value.toLowerCase());
            option.hidden = !isAvailable;
            if (isAvailable && !firstAvailable) firstAvailable = option.value;
        });

        const currentOption = typeSelect.querySelector(`option[value="${typeSelect.value}"]`);
        if (currentOption && currentOption.hidden) {
            typeSelect.value = (availableEnergies.length > 1) ? 'all' : (firstAvailable || 'all');
        }
    }

    function handleInterfaceRules() {
        const isComparing = compareSelect.value !== "";
        secondaryRadios.forEach(radio => {
            if (radio.value !== 'none') {
                radio.disabled = isComparing;
                const label = radio.parentElement;
                if (isComparing) label.classList.add('opacity-50', 'cursor-not-allowed');
                else label.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
        if (isComparing) noneRadio.checked = true;
    }

    compareSelect.addEventListener('change', handleInterfaceRules);
    citySelect.addEventListener('change', updateTypeOptions);
    
    // Si on est admin, on écoute le bouton Backtest
    // Gestion visuelle et fonctionnelle du bouton Backtest
    const backtestToggle = document.getElementById('backtestToggle');
    const backtestLabel = document.getElementById('backtestLabel');
    const backtestContainer = document.getElementById('backtestContainer');

    if (backtestToggle) {
        backtestToggle.addEventListener('change', (e) => {
            // Changement visuel immédiat
            if (e.target.checked) {
                if (backtestLabel) backtestLabel.innerHTML = 'Mode Backtest : <span class="text-green-600 font-black">ACTIF</span>';
                if (backtestContainer) backtestContainer.classList.add('bg-green-50', 'border-green-300');
            } else {
                if (backtestLabel) backtestLabel.innerHTML = 'Mode Backtest : <strong>INACTIF</strong>';
                if (backtestContainer) backtestContainer.classList.remove('bg-green-50', 'border-green-300');
            }
            // On recharge le graphique
            refreshChart();
        });
    }

    async function refreshChart() {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        const type = formData.get('type');
        handleInterfaceRules();

        // Récupération sécurisée des canvas
        const canvasStandard = document.getElementById('energyChartStandard');
        const canvasLSTM = document.getElementById('energyChartLSTM');
        const canvasUser = document.getElementById('energyChart');
        
        const isBacktest = backtestToggle && backtestToggle.checked;

        try {
            if (canvasStandard && canvasLSTM) {
                // =====================================
                // MODE ADMINISTRATEUR (2 Graphiques)
                // =====================================
                if (isBacktest) {
                    document.getElementById('errorStd').classList.remove('hidden');
                    document.getElementById('errorLstm').classList.remove('hidden');
                    
                    const req = await fetch(`/api/energy?${params}&backtest=true`);
                    const json = await req.json();
                    
                    updateBacktestChart(json, 'energyChartStandard', 'standard', 'errorStd');
                    updateBacktestChart(json, 'energyChartLSTM', 'lstm', 'errorLstm');
                } else {
                    document.getElementById('errorStd').classList.add('hidden');
                    document.getElementById('errorLstm').classList.add('hidden');
                    
                    const reqStd = await fetch(`/api/energy?${params}&algo=standard`);
                    const reqLstm = await fetch(`/api/energy?${params}&algo=lstm`);
                    
                    updateChart(await reqStd.json(), type, 'energyChartStandard', 'chartTitleStd', 'Modèle Standard');
                    updateChart(await reqLstm.json(), type, 'energyChartLSTM', 'chartTitleLstm', 'Modèle LSTM');
                }
            } else if (canvasUser) {
                // =====================================
                // MODE UTILISATEUR NORMAL (1 Graphique)
                // =====================================
                const req = await fetch(`/api/energy?${params}`);
                if (!req.ok) throw new Error("Erreur serveur API");
                const json = await req.json();
                
                updateChart(json, type, 'energyChart', 'chartTitle', 'Production');
            }
        } catch (e) {
            console.error(e);
            const titleEl = document.getElementById('chartTitle') || document.getElementById('chartTitleStd');
            if (titleEl) titleEl.textContent = "Erreur de chargement des données";
        }
    }

    // Fonction de dessin classique
    function updateChart(jsonData, type, canvasId, titleId, baseTitle) {
        if (chartInstances[canvasId]) chartInstances[canvasId].destroy();

        const ctxTarget = document.getElementById(canvasId);
        if(!ctxTarget) return; // Sécurité anti-crash
        
        const titleTarget = document.getElementById(titleId);

        const rawData = jsonData.data || [];
        const mainCity = jsonData.city || 'all';
        const formData = new FormData(document.getElementById('energyForm'));
        const compareCity = formData.get('compare');
        
        const secondaryModeEl = document.querySelector('input[name="secondaryView"]:checked');
        const secondaryMode = secondaryModeEl ? secondaryModeEl.value : 'none';
        
        // Copie du thème pour ne pas modifier l'original
        let theme = Object.assign({}, themes[type] || themes['all']);
        
        // Couleur rouge pour le LSTM
        if(canvasId === 'energyChartLSTM') {
            theme.color = '#ef4444';
            theme.bg = 'rgba(239, 68, 68, 0.2)';
        }

        let typeLabel = (type === 'all') ? 'Totale' : type.charAt(0).toUpperCase() + type.slice(1);
        let cityText = (mainCity === 'all') ? 'Toutes zones' : mainCity;
        if(titleTarget) titleTarget.textContent = `${baseTitle} - ${typeLabel} (${cityText})`;

        let dates = [];
        let dataCity1 = [], dataSecondary = [], dataCity2 = [];

        rawData.forEach(item => { if (!dates.includes(item.date)) dates.push(item.date); });
        dates.sort();

        let isPrediction = false;

        dates.forEach(date => {
            const points1 = rawData.filter(d => d.date === date && d.ville.toLowerCase() === mainCity.toLowerCase());
            const totalProd1 = points1.reduce((sum, p) => sum + parseFloat(p.production), 0);
            dataCity1.push(totalProd1);

            if(points1.some(p => p.statut && p.statut.startsWith('prevision'))) isPrediction = true;

            let secValue = 0;
            if (points1.length > 0) {
                if (secondaryMode === 'meteo') secValue = points1.reduce((max, p) => Math.max(max, parseFloat(p.meteo || 0)), 0);
                else if (secondaryMode === 'temp') {
                    const pWithTemp = points1.find(p => p.temp !== 0 && p.temp !== null); 
                    secValue = pWithTemp ? parseFloat(pWithTemp.temp) : 0;
                }
            }
            dataSecondary.push(secValue);

            if (compareCity) {
                const points2 = rawData.filter(d => d.date === date && d.ville.toLowerCase() === compareCity.toLowerCase());
                dataCity2.push(points2.reduce((sum, p) => sum + parseFloat(p.production), 0));
            }
        });
    
        let datasets = [{
            label: `Production ${mainCity}`,
            data: dataCity1,
            borderColor: theme.color,
            backgroundColor: theme.bg,
            yAxisID: 'y',
            tension: 0.4,
            fill: true,
            borderDash: isPrediction ? [5, 5] : [],
        }];

        if (secondaryMode === 'meteo') datasets.push({ label: 'Indice Météo', data: dataSecondary, borderColor: '#fbbf24', borderDash: [5, 5], pointRadius: 0, yAxisID: 'y1', tension: 0.1, fill: false });
        else if (secondaryMode === 'temp') datasets.push({ label: 'Température (°C)', data: dataSecondary, borderColor: '#ef4444', borderWidth: 2, borderDash: [5, 5], pointRadius: 0, yAxisID: 'y1', tension: 0.4, fill: false });

        if (compareCity && compareCity !== "") datasets.push({ label: `Production ${compareCity}`, data: dataCity2, borderColor: '#9ca3af', borderWidth: 2, yAxisID: 'y', tension: 0.3, fill: false });

        chartInstances[canvasId] = new Chart(ctxTarget.getContext('2d'), {
            type: 'line',
            data: { labels: dates, datasets: datasets },
            options: {
                responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { type: 'linear', position: 'left', beginAtZero: true, title: { display: true, text: 'Production (kW)' } },
                    y1: { type: 'linear', position: 'right', display: (secondaryMode !== 'none'), grid: { drawOnChartArea: false }, title: { display: true, text: secondaryMode === 'temp' ? 'Température (°C)' : 'Indice Météo' } }
                }
            }
        });
    }

    // Fonction de dessin BACKTEST (Exclusif Admin)
    function updateBacktestChart(jsonData, canvasId, targetAlgo, errorSpanId) {
        if (chartInstances[canvasId]) chartInstances[canvasId].destroy();

        const ctxTarget = document.getElementById(canvasId);
        if(!ctxTarget) return;

        const rawData = jsonData.data || [];
        const mainCity = jsonData.city;
        
        let dates = [...new Set(rawData.map(item => item.date))].sort();
        
        let dataReal = [], dataPredicted = [];
        let errorSum = 0, errorCount = 0;

        dates.forEach(date => {
            const pointsDate = rawData.filter(d => d.date === date && d.ville.toLowerCase() === mainCity.toLowerCase());
            
            const realPoints = pointsDate.filter(p => p.statut === 'reel');
            const realValue = realPoints.length > 0 ? realPoints.reduce((sum, p) => sum + parseFloat(p.production), 0) : null;
            
            const prevPoints = pointsDate.filter(p => p.statut === 'prevision' && p.algo === targetAlgo);
            const prevValue = prevPoints.length > 0 ? prevPoints.reduce((sum, p) => sum + parseFloat(p.production), 0) : null;

            dataReal.push(realValue);
            dataPredicted.push(prevValue);

            if (realValue !== null && prevValue !== null && realValue > 0) {
                errorSum += Math.abs(realValue - prevValue);
                errorCount++;
            }
        });

        const errorElement = document.getElementById(errorSpanId);
        if(errorElement) {
            if (errorCount > 0) {
                const avgError = (errorSum / errorCount).toFixed(2);
                errorElement.innerHTML = `⚠️ Écart moyen : <strong>${avgError} kW</strong>`;
            } else {
                errorElement.innerHTML = `Aucune donnée réelle à comparer`;
            }
        }
        
        let datasets = [
            {
                label: `Données réelles (${mainCity})`,
                data: dataReal,
                borderColor: '#111827',
                backgroundColor: 'rgba(17, 24, 39, 0.1)',
                borderWidth: 3,
                tension: 0.3,
                fill: true
            },
            {
                label: `Prédiction ${targetAlgo.toUpperCase()}`,
                data: dataPredicted,
                borderColor: (targetAlgo === 'lstm') ? '#ef4444' : '#f59e0b',
                borderWidth: 2,
                borderDash: [5, 5],
                tension: 0.4,
                fill: false
            }
        ];

        chartInstances[canvasId] = new Chart(ctxTarget.getContext('2d'), {
            type: 'line',
            data: { labels: dates, datasets: datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false }
            }
        });
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        refreshChart();
    });

    // Initialisation au chargement de la page
    handleInterfaceRules();
    updateTypeOptions();
    refreshChart();
});