<?php
/**
 * Fichier : Layout.php
 * Rôle : Fournit un modèle réutilisable pour les views (Header + view + Footer)
 * Auteur : Lucas LEPAPE, Gustin MAILHE
 */

namespace App\Core;

/**
 * Classe Layout qui gère le rendu complet des pages
 * (Header + view + footer)
 */
class Layout {

    /** @var string $viewPath Chemin vers le fichier de la vue à afficher */
    private string $viewPath;

    /** @var string $title Titre de la page affichée dans la balise <title> */
    private string $title;

    /**
     * Constructeur de la classe Layout qui initialise le chemin vers la vue à afficher ainsi que le titre de la page
     * 
     * @param string $viewPath Chemin complet vers la vue
     * @param string $title Titre de la page (par défaut : 'EnergyDash')
     */
    public function __construct(string $viewPath, string $title = 'EnergyDash')
    {
        $this->viewPath = $viewPath;
        $this->title = $title;
    }

    /**
     * Affiche la page complète en incluant tout les fichiers necessaires
     * 
     * @return void
     */
    public function render(array $data = []): void {
        extract($data);
        $title = $this->title;
        require __DIR__ . '/../Views/Shared/Header.php';

        require $this->viewPath;

        require __DIR__ . '/../Views/Shared/Footer.php';
    }

    /**
     * Getter pour le titre
     * 
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }
}

