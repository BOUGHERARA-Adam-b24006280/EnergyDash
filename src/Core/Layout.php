<?php
/**
 * Fichier : Layout.php
 * Rôle : Gère l'affichage global (Header + Vue + Footer) et les outils de vue (Flash messages).
 * Auteur : Lucas LEPAPE, Gustin MAILHE
 */

namespace App\Core;

class Layout
{

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
    public function render(array $data = []): void
    {
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
    public function getTitle(): string
    {
        return $this->title;
    }


}