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
    public function index(): void
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $city = isset($_GET['city']) ? htmlspecialchars(trim($_GET['city'])) : null;
        $type = isset($_GET['type']) ? htmlspecialchars(trim($_GET['type'])) : null;
        $from = isset($_GET['from']) ? htmlspecialchars(trim($_GET['from'])) : null;
        $to   = isset($_GET['to'])   ? htmlspecialchars(trim($_GET['to']))   : null;

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
