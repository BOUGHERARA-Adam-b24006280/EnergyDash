<?php
/**
 * Fichier : FileUploadService.php
 * Rôle : Fichier contenant le service de gestion des téléversements (upload).
 */

namespace App\Services;

/**
 * Service gérant le téléversement et la suppression sécurisé des fichiers CSV utilisateurs.
 */
class FileUploadService
{
    /** @var string Le chemin absolu vers le dossier où les fichiers CSV seront stockés. */
    private string $storageDir;

    /** Constructeur de service. Initialise le dossier de stockage de destination. */
    public function __construct()
    {
        $this->storageDir = __DIR__ . '/../../Storage';
    }

    /**
     * Gère l'upload d'un fichier CSV, effectue des vérifications de sécurité (extension, type MIME) et déplace le fichier.
     * @param array<string, mixed> $file Le tableau contenant les informations du fichier uplaodé.
     * @param int $userId L'identifiant de l'utilisateur qui téléverse le fichier.
     * @return void
     * @throws \Exception En cas d'erreur de transfert (code erreur HTTP), d'extension incorrecte, de type MIME invalide ou d'erreur d'écriture.
     */
    public function handleCsvUpload(array $file, int $userId): void
    {
        $errorVal = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        $error = is_numeric($errorVal) ? (int)$errorVal : UPLOAD_ERR_NO_FILE;

        $nameVal = $file['name'] ?? '';
        $name = is_string($nameVal) ? $nameVal : '';

        $tmpNameVal = $file['tmp_name'] ?? '';
        $tmpName = is_string($tmpNameVal) ? $tmpNameVal : '';

        if ($error !== UPLOAD_ERR_OK) {
            throw new \Exception("Erreur lors du transfert du fichier (Code: $error)");
        }

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'csv') {
            throw new \Exception("Format incorrect. Veuillez envoyer un fichier .csv");
        }

        $finfo = \finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new \Exception("Erreur interne lors de l'analyse du fichier.");
        }

        $mimeType = \finfo_file($finfo, $tmpName);
        $allowedMimes = [
            'text/csv', 'text/plain', 'application/vnd.ms-excel', 
            'application/csv', 'text/x-csv', 'application/x-csv', 
            'text/x-comma-separated-values', 'text/comma-separated-values'
        ];

        if ($mimeType === false || !in_array($mimeType, $allowedMimes)) {
            throw new \Exception("Fichier invalide détecté. Seuls les vrais CSV sont acceptés.");
        }

        $targetPath = $this->storageDir . '/energy_user_' . $userId . '.csv';

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new \Exception("Impossible d'écrire le fichier sur le disque.");
        }
    }

    /**
     * Supprime physiquement le fichier CSV associé à un utilisateur spécifique de l'espace de stockage.
     * @param int $userId L'identifiant de l'utilisateur dont on veut supprimer le fichier CSV.
     * @return bool Retourne true si le fichier a bien été supprimé, false sinon (ou s'il n'existait pas).
     */
    public function deleteUserCsv(int $userId): bool
    {
        $targetPath = $this->storageDir . '/energy_user_' . $userId . '.csv';
        if (file_exists($targetPath)) {
            return unlink($targetPath);
        }
        return false;
    }
}