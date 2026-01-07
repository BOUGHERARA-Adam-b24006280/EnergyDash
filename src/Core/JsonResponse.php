<?php
/**
 * Fichier : JsonResponse.php
 * Rôle : Helper pour envoyer des réponses au format JSON (utilisé par les API).
 * Auteur : L'équipe EnergyDash
 */

namespace App\Core;

/**
 * Classe JsonResponse
 * Helper pour l'envoi de réponses API au format JSON.
 *
 * @package App\Core
 */
class JsonResponse
{
    /**
     * Envoie une réponse JSON standard avec un code HTTP.
     *
     * @param mixed $data Les données à encoder en JSON (tableau, objet, etc.)
     * @param int $status Le code HTTP (200 par défaut)
     * @return void
     */
    public static function send(mixed $data, int $status = 200): void
    {
        // Nettoie le tampon de sortie pour éviter que du HTML ne se glisse dans le JSON
        if (ob_get_length()) {
            ob_clean();
        }

        // Définit l'en-tête pour dire au navigateur "Ceci est du JSON"
        header('Content-Type: application/json; charset=utf-8');
        
        // Définit le code de réponse (200 OK, 404 Not Found, etc.)
        http_response_code($status);

        // Encode les données et les affiche
        echo json_encode($data);
        
        // Arrête le script immédiatement pour garantir qu'rien d'autre n'est envoyé
        exit;
    }

    /**
     * Envoie une réponse d'erreur formatée en JSON.
     * Utile pour les blocs catch() dans les contrôleurs.
     *
     * @param string $message Le message d'erreur
     * @param int $status Le code HTTP d'erreur (400, 404, 500...)
     * @return void
     */
    public static function error(string $message, int $status = 400): void
    {
        self::send(['error' => $message], $status);
    }
}