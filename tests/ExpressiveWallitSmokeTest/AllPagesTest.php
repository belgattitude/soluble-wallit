<?php

declare(strict_types=1);

namespace ExpressiveWallitSmokeTest;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class AllPagesTest extends TestCase
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

    /**
     * @group        functional
     * @dataProvider urlProvider
     *
     * @param string $method
     * @param string $url
     * @param string $status_code
     */
    public function testAllRoutes(string $method, string $url, string $status_code): void
    {
        $response = $this->client->request($method, $url, [
            'exceptions' => false
        ]);

        $this->assertEquals($status_code, $response->getStatusCode());
        $this->assertNotEmpty($response->getBody()->getContents());
    }

    /**
     * @return array
     */
    public function urlProvider(): array
    {
        return [
            ['GET', '/',    StatusCode::STATUS_OK],
            ['GET', '/404', StatusCode::STATUS_NOT_FOUND],
        ];
    }
}
