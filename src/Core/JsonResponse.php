<?php
/**
 * Fichier : JsonResponse.php
 * Rôle : 
 * Auteur : Lucas LEPAPE, Adam Bougherara
 */

namespace App\Core;

/**
 * Classe JsonResponse qui gère les réponses API
 */
class JsonResponse {
    public static function send(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message, int $status = 400): void {
        self::send(['error' => $message], $status);
    }
}
