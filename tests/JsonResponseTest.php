<?php

namespace App\Core;

$mockHeaders = [];
$mockResponseCode = 200;

function header(string $string): void
{
    global $mockHeaders;
    $mockHeaders[] = $string;
}

function http_response_code(?int $code = null): int|bool
{
    global $mockResponseCode;
    if ($code !== null) {
        $mockResponseCode = $code;
    }
    return $mockResponseCode;
}

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use App\Core\JsonResponse;

class JsonResponseTest extends TestCase
{
    protected function setUp(): void
    {
        global $mockHeaders, $mockResponseCode;
        $mockHeaders = [];
        $mockResponseCode = 200;

        JsonResponse::$exitAfterSend = false;
    }

    /**
     * Test de la méthode send() : Cas nominal
     */
    public function testSendOutputsJsonAndSetsHeaders(): void
    {
        $data = ['id' => 123, 'name' => 'Test'];

        ob_start();
        JsonResponse::send($data, 201);
        $output = ob_get_clean();

        global $mockResponseCode;
        $this->assertEquals(201, $mockResponseCode, "Le code HTTP devrait être 201");

        global $mockHeaders;
        $this->assertContains('Content-Type: application/json; charset=utf-8', $mockHeaders, "Le header Content-Type doit être présent");

        $this->assertJsonStringEqualsJsonString(json_encode($data), $output, "Le JSON retourné doit correspondre aux données");
    }

    /**
     * Test de la méthode error()
     */
    public function testErrorOutputsFormattedJson(): void
    {
        ob_start();
        JsonResponse::error("Accès interdit", 403);
        $output = ob_get_clean();

        global $mockResponseCode;
        $this->assertEquals(403, $mockResponseCode);

        $expected = json_encode(['error' => 'Accès interdit']);
        
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    /**
     * Test : Vérifie que le tampon de sortie est nettoyé avant l'envoi
     */
    public function testSendCleansOutputBuffer(): void
    {
        ob_start();
        echo "<div>Mauvais HTML qui traîne</div>";
        
        JsonResponse::send(['status' => 'ok']);
        
        $output = ob_get_clean();

        $this->assertStringNotContainsString("<div>Mauvais HTML", $output);
        
        $this->assertJson($output);
    }
}