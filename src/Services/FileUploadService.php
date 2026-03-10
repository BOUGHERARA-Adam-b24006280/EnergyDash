<?php
namespace App\Services;

class FileUploadService
{
    private string $storageDir;

    public function __construct()
    {
        $this->storageDir = __DIR__ . '/../../Storage';
    }

    /**
     * Gère l'upload d'un fichier CSV.
     * @throws \Exception En cas d'erreur ou de fichier invalide.
     */
    public function handleCsvUpload(array $file, int $userId): void
    {
        $error = (int)$file['error'];
        $name = (string)$file['name'];
        $tmpName = (string)$file['tmp_name'];

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
     * Supprime le fichier CSV d'un utilisateur
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