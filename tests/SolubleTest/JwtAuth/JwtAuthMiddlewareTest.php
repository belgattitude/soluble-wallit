<?php

declare(strict_types=1);

namespace SolubleTest\JwtAuth;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\JwtAuth\JwtAuthMiddleware;
use Soluble\JwtAuth\Service\JwtService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class JwtAuthMiddlewareTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testAuthTokenFromCookie()
    {
        $jwtMw = $this->getDefaultJwtAuthMiddleware();

        $token = $this->getDefaultJwtService()->createToken(['uid' => 10]);

        $delegate = $this->getMockedDelegate(function (ServerRequestInterface $request) {
            $data = $request->getAttribute(JwtAuthMiddleware::class);

            self::assertInstanceOf(Token::class, $data);

            return (new Response())->withAddedHeader('test', 'passed');
        });

        $response = $jwtMw->process(
            (new ServerRequest())
                ->withCookieParams(['jwtcookie' => (string) $token]
            ), $delegate);

        $this->assertContains('passed', $response->getHeader('test'));
    }

    public function testAuthTokenFromAuthenticationHeader()
    {
        $jwtMw = $this->getDefaultJwtAuthMiddleware();

        $token = $this->getDefaultJwtService()->createToken(['uid' => 10]);

        $delegate = $this->getMockedDelegate(function (ServerRequestInterface $request) {
            $data = $request->getAttribute(JwtAuthMiddleware::class);
            self::assertInstanceOf(Token::class, $data);

            return (new Response())->withAddedHeader('test', 'passed');
        });

        $response = $jwtMw->process(
            (new ServerRequest())
                ->withAddedHeader('Authentication', "Bearer $token"), $delegate);

        $this->assertContains('passed', $response->getHeader('test'));
    }

    public function testInvalidTokenFromAuthenticationHeader()
    {
        $jwtMw = $this->getDefaultJwtAuthMiddleware();

        $token = 'aninvalidToken';

        $delegate = $this->createMock(DelegateInterface::class);
        $delegate->expects($this->never())->method('process');

        $response = $jwtMw->process(
            (new ServerRequest())
                ->withAddedHeader('Authentication', "Bearer $token"), $delegate);

        $this->assertEquals(401, $response->getStatusCode());

        self::assertInstanceOf(Response\JsonResponse::class, $response);
    }

    public function testExpiredTokenFromCookieHeader()
    {
        $jwtMw = $this->getDefaultJwtAuthMiddleware();
        $expiration = new \DateTime('-1 day');

        $token = $this->getDefaultJwtService()->createToken(['uid' => 10], $expiration->getTimestamp());

        $delegate = $this->createMock(DelegateInterface::class);
        $delegate->expects($this->never())->method('process');

        $response = $jwtMw->process(
            (new ServerRequest())
                ->withCookieParams(['jwtcookie' => (string) $token]
                ), $delegate);

        $this->assertEquals(401, $response->getStatusCode());
        self::assertInstanceOf(Response\JsonResponse::class, $response);
        $this->assertContains('expired', json_decode($response->getBody()->getContents())->reason);
    }

    private function getDefaultJwtAuthMiddleware(): JwtAuthMiddleware
    {
        return new JwtAuthMiddleware($this->getDefaultJwtService());
    }

    private function getDefaultJwtService(): JwtService
    {
        return new JwtService(new Sha256(), 'my-secret-key');
    }

    /**
     * @param callable $callback
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|DelegateInterface
     */
    private function getMockedDelegate(callable $callback): DelegateInterface
    {
        $delegate = $this->createMock(DelegateInterface::class);
        $delegate->expects($this->once())
            ->method('process')
            ->willReturnCallback($callback)
            ->with(
                self::isInstanceOf(ServerRequestInterface::class)
            );

        return $delegate;
    }
}
