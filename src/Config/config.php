<?php
/**
 * Fichier : config.php
 * Rôle : Fichier de configuration principale de l'application.
 * Charge les variables d'environnement et retourne un tableau de configuration.
 */

require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Chargement du fichier .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
// Ne démarre pas si la BDD n'est pas configuré
$dotenv->required(['DATABASE_HOST', 'DATABASE_NAME', 'DATABASE_USER', 'DATABASE_PASSWORD'])->notEmpty();

// Définition de la constante de base
if (!defined('BASE_URL')) {
    define('BASE_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000');
}

// Sinon, décalage horaire et tokens systématiquement invalides
date_default_timezone_set('Europe/Paris');

// Configuration principale retournée sous forme de tableau
return [
    'env' => $_ENV['APP_ENV'],

    'database' => [
        'host' => $_ENV['DATABASE_HOST'],
        'name' => $_ENV['DATABASE_NAME'],
        'user' => $_ENV['DATABASE_USER'],
        'pass' => $_ENV['DATABASE_PASSWORD'],
    ],

    'smtp' => [
        'host' => $_ENV['SMTP_HOST'] ?? 'localhost',
        'port' => (int)($_ENV['SMTP_PORT'] ?? 1025), // on force le port à être un entier
        'username' => $_ENV['SMTP_USER'] ?? '',
        'password' => $_ENV['SMTP_PASS'] ?? '',
        'from' => $_ENV['SMTP_FROM'] ?? 'no-reply@localhost',
        'from_name' => $_ENV['SMTP_FROM_NAME'] ?? 'EnergyDash',
    ],
];