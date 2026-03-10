<?php
namespace App\Contracts;

/**
 * Interface que tous les algorithmes de prédiction devront respecter.
 */
interface PredictionStrategyInterface 
{
    public function predict(string $type, string $city, string $startDate, string $endDate): array;
}