<?php
/**
 * Fichier : dashboard.php
 * Rôle : Affiche les données énergétiques du dashboard
 * Auteur : Lucas LEPAPE, Adam Bougherara
 */
?>

<div class="container mt-5 pt-5">
    <h1 class="fw-bold mb-4">Tableau de bord énergétique</h1>

    <form id="energyForm" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="city" class="form-control" placeholder="Ville (ex: aix-en-provence)" required>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select" required>
                <option value="">Type d'énergie</option>
                <option value="solar">Solaire</option>
                <option value="wind">Éolien</option>
                <option value="hydro">Hydraulique</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="from" class="form-control" required>
        </div>
        <div class="col-md-2">
            <input type="date" name="to" class="form-control" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Afficher</button>
        </div>
    </form>

    <div id="info" class="mb-3"></div>
    <canvas id="energyChart" height="150"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const form = document.getElementById('energyForm');
const ctx = document.getElementById('energyChart').getContext('2d');
let chart;

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const params = new URLSearchParams(new FormData(form)).toString();
    document.getElementById('info').innerHTML = "Chargement des données...";

    try {
        const res = await fetch(`/api/energy?${params}`);
        const data = await res.json();

        if (res.status !== 200) throw new Error(data.error || 'Erreur inconnue');

        document.getElementById('info').innerHTML = `
            <h5>${data.asset} (${data.type})</h5>
            <p>Période : ${data.from} → ${data.to}</p>
        `;

        const labels = data.data.map(row => row.date);
        const production = data.data.map(row => row.production);
        const consumption = data.data.map(row => row.consumption);

        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Production (kWh)',
                        data: production,
                        borderColor: '#007bff',
                        tension: 0.3
                    },
                    {
                        label: 'Consommation (kWh)',
                        data: consumption,
                        borderColor: '#dc3545',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

    } catch (err) {
        document.getElementById('info').innerHTML =
            `<div class="alert alert-danger">${err.message}</div>`;
        if (chart) chart.destroy();
    }
});
</script>

