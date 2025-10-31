<?php
/**
 * Fichier : EnergyController.php
 * Rôle : 
 * Auteur : Lucas LEPAPE, Adam Bougherara
 */

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Models\EnergyModel;
use Exception;

/**
 * Classe EnergyController qui gère les vérifications, la clé API et le format des réponses
 */
class EnergyController
{
    private string $apiKey = 'energydash123';

    public function index(): void
    {
        // (Tu peux réactiver la sécurité plus tard)
        // $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        // if ($header !== 'Bearer ' . $this->apiKey) {
        //     JsonResponse::error('Clé API invalide', 401);
        // }

        // Récupération des paramètres
        $city = filter_input(INPUT_GET, 'city', FILTER_SANITIZE_STRING);
        $type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
        $from = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_STRING);
        $to   = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_STRING);

        if (!$city || !$type || !$from || !$to) {
            JsonResponse::error('Paramètres requis : city, type, from, to', 400);
        }

        try {
            $model = new EnergyModel();
            $data = $model->getEnergyData($city, $type, $from, $to);
            JsonResponse::send($data);
        } catch (Exception $e) {
            JsonResponse::error($e->getMessage(), 404);
        }
    }
}