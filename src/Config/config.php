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

// Définition de la constante de base
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000');
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
        'port' => $_ENV['SMTP_PORT'] ?? 1025,
        'username' => $_ENV['SMTP_USER'] ?? '',
        'password' => $_ENV['SMTP_PASS'] ?? '',
        'from' => $_ENV['SMTP_FROM'] ?? 'no-reply@localhost',
        'from_name' => $_ENV['SMTP_FROM_NAME'] ?? 'EnergyDash',
    ],
];