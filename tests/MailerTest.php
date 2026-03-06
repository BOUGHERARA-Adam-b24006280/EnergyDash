<?php

namespace Tests\Core;

class MailerTest extends \PHPUnit\Framework\TestCase {
    /**
     * Test : L'envoi d'email réussit (return true).
     */
    public function testSendReturnsTrueOnSuccess(): void {
        $mailer = (new \ReflectionClass(\App\Core\Mailer::class))->newInstanceWithoutConstructor();

        $phpMailerMock = $this->createMock(\PHPMailer\PHPMailer\PHPMailer::class);

        $phpMailerMock->expects($this->once())->method('send')->willReturn(true);

        $phpMailerMock->expects($this->once())->method('clearAddresses');
        $phpMailerMock->expects($this->once())->method('addAddress')->with('client@example.com');

        $reflection = new \ReflectionClass($mailer);
        $property = $reflection->getProperty('mail');
        $property->setValue($mailer, $phpMailerMock);

        $result = $mailer->send('client@example.com', 'Sujet Test', 'Corps du message');

        $this->assertTrue($result);
    }

    /**
     * Test : L'envoi d'email échoue (\PHPMailer\PHPMailer\Exception attrapée, return false).
     */
    public function testSendReturnsFalseOnException(): void {
        $mailer = (new \ReflectionClass(\App\Core\Mailer::class))->newInstanceWithoutConstructor();
        $phpMailerStub = $this->createStub(\PHPMailer\PHPMailer\PHPMailer::class);

        $phpMailerStub->method('send')->willThrowException(new \PHPMailer\PHPMailer\Exception("Erreur SMTP simulée"));

        $reflection = new \ReflectionClass($mailer);
        $property = $reflection->getProperty('mail');
        $property->setValue($mailer, $phpMailerStub);

        $result = $mailer->send('fail@example.com', 'Sujet', 'Body');

        $this->assertFalse($result);
    }
}