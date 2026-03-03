<?php
/**
 * Fichier : View.php
 * Rôle : Gère l'affichage global (Header + Vue + Footer) et les outils de vue (Flash messages).
 */

namespace App\Core;

/**
 * Classe View
 * Gère le rendu de la structure globale des pages (Header, Contenu, Footer).
 *
 * @package App\Core
 */
class View {
    /**
     * Affiche la page complète (Header + Vue + Footer).
     *
     * @param string $view Nom de la vue (ex: 'auth/login')
     * @param array<string, mixed> $data Données à extraire pour la vue.
     * @return void
     */
    public function render(string $view, array $data = []): void {
        $data['success'] = $data['success'] ?? $this->getFlash('success');
        $data['error'] = $data['error'] ?? $this->getFlash('error');

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        $title = $data['title'] ?? 'EnergyDash';

        if (!is_string($title)) $title = 'EnergyDash';

        extract($data);

        require __DIR__ . '/../Views/shared/header.php';
        require $viewPath;
        require __DIR__ . '/../Views/shared/footer.php';
    }

    /**
     * Récupère un message Flash et le supprime de la session.
     *
     * @param string $type Le type de message à récupérer.
     * @return string|null Le message ou null s'il n'existe pas.
     */
    protected function getFlash(string $type): ?string {
        if (isset($_SESSION[$type])) {
            $msg = $_SESSION[$type];
            unset($_SESSION[$type]);
            return is_string($msg) ? $msg : null;
        }
        return null;
    }
}