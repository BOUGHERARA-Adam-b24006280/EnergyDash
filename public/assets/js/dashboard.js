document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('energyForm');
    const ctx = document.getElementById('energyChart').getContext('2d');
    const titleEl = document.getElementById('chartTitle');
    
    const citySelect = document.querySelector('select[name="city"]');
    const typeSelect = document.querySelector('select[name="type"]');
    const compareSelect = document.querySelector('select[name="compare"]');
    const secondaryRadios = document.querySelectorAll('input[name="secondaryView"]');
    const noneRadio = document.querySelector('input[value="none"]');

    const energyMapping = window.energyMappingData || {};

    let chartInstance = null;

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
    citySelect.addEventListener('change', updateTypeOptions);


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
    updateTypeOptions();
    refreshChart();
});