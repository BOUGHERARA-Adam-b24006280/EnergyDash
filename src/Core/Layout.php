<?php
/**
 * Fichier : Layout.php
 * Rôle : Gère l'affichage global (Header + Vue + Footer) et les outils de vue (Flash messages).
 * Auteur : Lucas LEPAPE, Gustin MAILHE
 */

namespace App\Core;

/**
 * Classe Layout
 * Gère le rendu de la structure globale des pages (Header, Contenu, Footer).
 *
 * @package App\Core
 */
class Layout
{

    /** @var string $viewPath Chemin vers le fichier de la vue à afficher */
    private string $viewPath;

    /** @var string $title Titre de la page */
    private string $title;

    /**
     * Constructeur
     *
     * @param string $viewPath Chemin absolu vers le fichier de vue.
     * @param string $title Titre de la page (affiché dans la balise <title>).
     */
    public function __construct(string $viewPath, string $title = 'EnergyDash')
    {
        $this->viewPath = $viewPath;
        $this->title = $title;
    }

    /**
     * Affiche la page complète (Header + Vue + Footer).
     *
     * @param array $data Données à extraire pour la vue.
     * @return void
     */
    public function render(array $data = []): void
    {
        extract($data);
        $title = $this->title;
        require __DIR__ . '/../Views/shared/header.php';
        require $this->viewPath;
        require __DIR__ . '/../Views/shared/footer.php';
    }

    /**
     * Récupère le titre de la page
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}