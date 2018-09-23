<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Smoke;

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Client;
use Lcobucci\JWT\Token;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class AuthSmokeTest extends TestCase
{
    use SmokeContainerTrait;

    /** @var Client */
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => sprintf('http://%s:%s', EXPRESSIVE_SERVER_HOST, EXPRESSIVE_SERVER_PORT),
            'timeout'  => 5,
        ]);
    }

    protected function getLoginResponse(?string $login = null, ?string $password = null): ResponseInterface
    {
        $response = $this->client->request('post', '/auth', [
            'form_params' => [
                'login'       => $login ?? 'demo',
                'password'    => $password ?? 'demo',
                'remember_me' => 'on'
            ],
            'exceptions' => false
        ]);

        return $response;
    }

    protected function getValidAuthenticatedToken(): Token
    {
        $response = $this->getLoginResponse();
        $content = $response->getBody()->getContents();
        $decoded = \json_decode($content, true);
        $jwtService = $this->getJwtService();

        return $jwtService->parsePlainToken($decoded['access_token']);
    }

    public function testReceiveValidTokenAfterLogin(): void
    {
        $response = $this->getLoginResponse();
        self::assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $content = $response->getBody()->getContents();
        self::assertJson($content);
        $decoded = \json_decode($content, true);
        self::assertArrayHasKey('access_token', $decoded);

        $plainToken = $decoded['access_token'];

        $jwtService = $this->getJwtService();

        self::assertTrue($jwtService->verifyPlainToken($plainToken));

        $token = $jwtService->parsePlainToken($plainToken);
        self::assertSame('JWT', $token->getHeader('typ'));
        self::assertSame('demo', $token->getClaim('login'));
    }

    public function testCannotAccessToAdminPage(): void
    {
        $response = $this->client->request('get', '/admin', [
            'exceptions' => false
        ]);
        self::assertEquals(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testCanAccessAdminPageWithToken(): void
    {
        $validToken = $this->getValidAuthenticatedToken();
        $response = $this->client->request('get', '/admin', [
            'exceptions' => false,
            'headers'    => [
                'Authentication' => 'Bearer ' . $validToken->__toString(),
                'Accept'         => 'application/json',
            ]
        ]);
        self::assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertContains('Hello "demo" user.', $response->getBody()->getContents());
    }
}
