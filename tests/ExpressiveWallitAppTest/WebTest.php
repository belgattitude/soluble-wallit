<?php

declare(strict_types=1);

namespace ExpressiveWallitAppTest;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class WebTest extends TestCase
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
     * Dataprovider for all routes that should
     * return an http status code equals to 200.
     *
     * @return array
     */
    public function getRoutesWithStatusOkToTest(): array
    {
        return [
            ['/'],
        ];
    }

    /**
     * @dataProvider getRoutesWithStatusOkToTest
     *
     * @param string $url
     */
    public function testAllRoutesWithStatusOk(string $url): void
    {
        $response = $this->client->request('GET', $url);
        $this->assertEquals(StatusCode::STATUS_OK, $response->getStatusCode());
        $this->assertNotEmpty($response->getBody()->getContents());
    }
}
