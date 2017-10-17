<?php

declare(strict_types=1);

namespace ExpressiveWallitSmokeTest;

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class AuthSmokeTest extends TestCase
{
    /** @var Client */
    private $client;

    protected function setUp()
    {
        $this->client = new Client([
            'base_uri' => sprintf('http://%s:%s', WEB_SERVER_HOST, WEB_SERVER_PORT),
            'timeout'  => 5,
        ]);
    }

    public function testLogin(): void
    {
        $response = $this->client->request('post', '/auth', [
            'form_params' => [
                'login'       => 'demo',
                'password'    => 'demo',
                'remember_me' => 'on'
            ],
            'exceptions' => false
        ]);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $content = $response->getBody()->getContents();
        $this->assertJson($content);
        $decoded = \json_decode($content, true);
        $this->assertArrayHasKey('access_token', $decoded);
        $this->assertFalse(true, 'todo');

    }
}
