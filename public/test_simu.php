<?php
// Fichier : public/test_simu.php

// 1. On inclut manuellement le modèle
require_once __DIR__ . '/../src/Models/EnergyModel.php';

// 2. On simule une session (car le __construct du modèle en a besoin)
session_start();
$_SESSION['user'] = ['id' => 999, 'role' => 'admin'];

// 3. On lance le test
try {
    echo "<h1>Test Simulation Météo</h1>";
    
    $model = new \App\Models\EnergyModel();
    
    // On demande une date PASSÉE (17 Décembre 2023) pour tester l'API Archive
    $date = '2023-12-17';
    
    echo "Demande de simulation pour le : <strong>$date</strong> à Lyon...<br><br>";
    
    // Appel direct de la fonction
    $result = $model->simulateDataFromWeather('solaire', 'Lyon', $date, $date);
    
    echo "<h3>Résultat reçu :</h3>";
    
    if (empty($result['data'])) {
        echo "❌ <strong>VIDE !</strong> Le tableau 'data' est vide.<br>";
        echo "Vérifie ta connexion internet ou le SSL.";
    } else {
        echo "✅ <strong>SUCCÈS !</strong> " . count($result['data']) . " lignes générées.<br>";
        echo "<pre style='background:#f4f4f4; padding:10px;'>";
        // On affiche les 3 premières lignes
        print_r(array_slice($result['data'], 0, 3));
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "❌ ERREUR CRITIQUE : " . $e->getMessage();
}