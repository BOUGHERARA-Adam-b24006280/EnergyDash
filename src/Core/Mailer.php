<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Classe Mailer
 * Gère l'envoi d'e-mails via SMTP via la librairie PHPMailer.
 * Charge la configuration depuis Config/config.php.
 *
 * @package App\Core
 */
class Mailer {
    /** @var PHPMailer Instance de PHPMailer */
    private PHPMailer $mail;

    /**
     * Constructeur.
     * Configure le serveur SMTP avec les paramètres de l'application.
     */
    public function __construct() {
        $this->mail = new PHPMailer(true);

        /** @var array{smtp: array{host: string, port: int, username: string, password: string, from: string, from_name: string}, env?: string} $config*/
        $config = require __DIR__ . '/../Config/config.php';
        /** @var array{host: string, port: int, username: string, password: string, from: string, from_name: string} $smtp */
        $smtp = $config['smtp'];

        try {
            $this->mail->isSMTP();
            $this->mail->Host = $smtp['host'];
            $this->mail->Port = $smtp['port'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $smtp['username'];
            $this->mail->Password = $smtp['password'];
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $this->mail->setFrom($smtp['from'], $smtp['from_name']);
            $this->mail->isHTML(false);
            $this->mail->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Erreur configuration Mailer : " . $e->getMessage());
        }
    }

    /**
     * Envoie un e-mail
     *
     * @param string $to      Adresse e-mail du destinataire
     * @param string $subject Sujet du message
     * @param string $body    Contenu du message (texte brut)
     * @return bool           True si envoyé, false sinon
     */
    public function send(string $to, string $subject, string $body): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi du mail : " . $e->getMessage());
            return false;
        }
    }
}