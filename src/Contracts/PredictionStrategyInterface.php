<?php
/**
 * Fichier : PredictionStrategyInterface.php
 * Rôle : Définit le contrat obligatoire pour tous les algorithmes de prédiction
 */

namespace App\Contracts;

/**
 * Interface que tous les algorithmes de prédiction devront respecter.
 */
interface PredictionStrategyInterface 
{

    /**
     * Exécute le calcul des prévisions énergétiques.
     * @param string $type Le type d'énergie à prédire.
     * @param string $city Le nom de la ville concernée par la prédiction.
     * @param string $startDate La date de début de la période de prédiction (format Y-m-d).
     * @param string $endDate La date de fin de la période de prédiction (format Y-m-d).
     * @return array<string, mixed> Un tableau contenant les métadonnées et la liste des points de données prédits.
     */
    public function predict(string $type, string $city, string $startDate, string $endDate): array;
}