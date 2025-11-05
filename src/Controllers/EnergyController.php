<?php
/**
 * Fichier : EnergyController.php
 * Rôle : 
 * Auteur : Lucas LEPAPE, Adam Bougherara
 */
namespace App\Controllers;

use App\Core\Controller;
use App\Core\JsonResponse;
use App\Models\EnergyModel;
use Exception;

/**
 * Classe EnergyController qui gère les vérifications, la clé API et le format des réponses
 */
class EnergyController extends Controller
{
    public function index(): void
    {
        $city = $this->sanitize($_GET['city'] ?? '');
        $type = $this->sanitize($_GET['type'] ?? '');
        $from = $this->sanitize($_GET['from'] ?? '');
        $to   = $this->sanitize($_GET['to'] ?? '');

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