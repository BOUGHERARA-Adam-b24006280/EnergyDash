<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Mailer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerTest extends TestCase
{
    /**
     * Cette méthode s'assure qu'un fichier de config existe pour que le constructeur
     * de Mailer ne plante pas avec un "Fatal Error: require... failed".
     */
    public static function setUpBeforeClass(): void
    {
        $root = __DIR__;
        while (!is_dir($root . '/src') && dirname($root) !== $root) {
            $root = dirname($root);
        }

        $configDir = $root . '/src/Config';
        $configPath = $configDir . '/config.php';

        if (!is_dir($configDir)) {
            mkdir($configDir, 0777, true);
        }

        if (!file_exists($configPath)) {
            $content = "<?php return ['smtp' => ['host'=>'localhost','port'=>1025,'username'=>'','password'=>'','from'=>'test@test.com','from_name'=>'Test']];";
            file_put_contents($configPath, $content);
        }
    }

    /**
     * Test : L'envoi d'email réussit (return true).
     */
    public function testSendReturnsTrueOnSuccess(): void
    {
        $mailer = new Mailer();

        $phpMailerMock = $this->createMock(PHPMailer::class);

        $phpMailerMock->expects($this->once())->method('send')->willReturn(true);

        $phpMailerMock->expects($this->once())->method('clearAddresses');
        $phpMailerMock->expects($this->once())->method('addAddress')->with('client@example.com');

        $reflection = new \ReflectionClass($mailer);
        $property = $reflection->getProperty('mail');
        $property->setAccessible(true);
        $property->setValue($mailer, $phpMailerMock);

        $result = $mailer->send('client@example.com', 'Sujet Test', 'Corps du message');

        $this->assertTrue($result);
    }

    /**
     * Test : L'envoi d'email échoue (Exception attrapée, return false).
     */
    public function testSendReturnsFalseOnException(): void
    {
        $mailer = new Mailer();
        $phpMailerMock = $this->createMock(PHPMailer::class);

        $phpMailerMock->method('send')->willThrowException(new Exception("Erreur SMTP simulée"));

        $reflection = new \ReflectionClass($mailer);
        $property = $reflection->getProperty('mail');
        $property->setAccessible(true);
        $property->setValue($mailer, $phpMailerMock);

        $result = $mailer->send('fail@example.com', 'Sujet', 'Body');

        $this->assertFalse($result);
    }
}