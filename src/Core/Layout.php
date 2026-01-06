<?php
/**
 * Fichier : Layout.php
 * Rôle : Gère l'affichage global (Header + Vue + Footer) et les outils de vue (Flash messages).
 * Auteur : Lucas LEPAPE, Gustin MAILHE
 */

namespace App\Core;

class Layout {

    /** @var string $viewPath Chemin vers le fichier de la vue à afficher */
    private string $viewPath;

    /** @var string $title Titre de la page */
    private string $title;

    /**
     * Constructeur
     */
    public function __construct(string $viewPath, string $title = 'EnergyDash')
    {
        $this->viewPath = $viewPath;
        $this->title = $title;
    }

    /**
     * Affiche la page complète
     */
    public function render(array $data = []): void {
        // Extrait les variables pour qu'elles soient accessibles dans la vue (ex: $cities)
        extract($data);
        
        // Rend le titre accessible
        $title = $this->title;
        
        // Inclut les parties de la page
        // Assurez-vous que le chemin 'shared' existe bien dans Views
        require __DIR__ . '/../Views/shared/header.php';
        require $this->viewPath;
        require __DIR__ . '/../Views/shared/footer.php';
    }

    /**
     * Récupère le titre de la page
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * AJOUT CRITIQUE : Permet de récupérer les messages Flash dans les vues.
     * C'est cette méthode qui manquait et causait votre erreur.
     * * @param string $key La clé du message (ex: 'success', 'error')
     * @return string|null Le message ou null s'il n'y en a pas
     */
    public function getFlash(string $key): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION[$key])) {
            $msg = $_SESSION[$key];
            // On supprime le message après l'avoir lu pour qu'il ne s'affiche qu'une fois
            unset($_SESSION[$key]);
            return $msg;
        }

        return null;
    }
}