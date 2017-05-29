<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Token\Provider as TokenProvider;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class JwtAuthMiddlewareTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testAuthTokenFromCookie(): void
    {
        $jwtMw = $this->getDefaultJwtAuthMiddleware();

        $token = $this->getDefaultJwtService()->createToken(['uid' => 10]);

        $delegate = $this->getMockedDelegate(function (ServerRequestInterface $request) {
            $data = $request->getAttribute(JwtAuthMiddleware::class);
            self::assertInstanceOf(Token::class, $data);

            return (new Response())->withAddedHeader('test', 'passed');
        });

        $cookieName = TokenProvider\ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName'];
        $response = $jwtMw->process(
            (new ServerRequest())
                ->withCookieParams([$cookieName => (string) $token]
            ), $delegate);

        $this->assertContains('passed', $response->getHeader('test'));
    }

    public function testAuthTokenFromAuthenticationHeader(): void
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

    public function testNotParseableTokenFromAuthenticationHeader(): void
    {
        $jwtMw = $this->getDefaultJwtAuthMiddleware();

        $token = 'aninvalidToken';

        /* For PHPSTAN
         * @var DelegateInterface|\PHPUnit_Framework_MockObject_MockObject $delegate
         */
        $delegate = $this->createMock(DelegateInterface::class);
        $delegate->expects($this->never())->method('process');

        $response = $jwtMw->process(
            (new ServerRequest())
                ->withAddedHeader('Authentication', "Bearer $token"), $delegate);

        $this->assertEquals(401, $response->getStatusCode());

        self::assertInstanceOf(Response\JsonResponse::class, $response);
    }

    public function testInvalidSignatureToken(): void
    {
        $jwtMw = $this->getDefaultJwtAuthMiddleware();

        $tokenString = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';

        $token = $this->getDefaultJwtService()->parsePlainToken($tokenString);

        $delegate = $this->createMock(DelegateInterface::class);
        $delegate->expects($this->never())->method('process');

        $cookieName = TokenProvider\ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName'];
        $response = $jwtMw->process(
            (new ServerRequest())
                ->withCookieParams([$cookieName => (string) $token]
                ), $delegate);

        $this->assertEquals(401, $response->getStatusCode());
        self::assertInstanceOf(Response\JsonResponse::class, $response);
        $this->assertContains('invalid', json_decode($response->getBody()->getContents())->reason);
    }

    public function testNoToken(): void
    {
        $jwtMw = $this->getDefaultJwtAuthMiddleware();

        $delegate = $this->createMock(DelegateInterface::class);
        $delegate->expects($this->never())->method('process');

        $response = $jwtMw->process(new ServerRequest(), $delegate);

        $this->assertEquals(401, $response->getStatusCode());
        self::assertInstanceOf(Response\JsonResponse::class, $response);
        $this->assertContains('No token', json_decode($response->getBody()->getContents())->reason);
    }

    public function testExpiredTokenFromCookieHeader()
    {
        $jwtMw = $this->getDefaultJwtAuthMiddleware();
        $expiration = new \DateTime('-1 day');

        $token = $this->getDefaultJwtService()->createToken(['uid' => 10], $expiration->getTimestamp());

        $delegate = $this->createMock(DelegateInterface::class);
        $delegate->expects($this->never())->method('process');

        $cookieName = TokenProvider\ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName'];
        $response = $jwtMw->process(
            (new ServerRequest())
                ->withCookieParams([$cookieName => (string) $token]
                ), $delegate);

        $this->assertEquals(401, $response->getStatusCode());
        self::assertInstanceOf(Response\JsonResponse::class, $response);
        $this->assertContains('expired', json_decode($response->getBody()->getContents())->reason);
    }

    private function getDefaultJwtAuthMiddleware(array $tokenProviders = null): JwtAuthMiddleware
    {
        if ($tokenProviders === null) {
            $tokenProviders = [
                [TokenProvider\ServerRequestAuthBearerProvider::class => [
                    'httpHeader'       => TokenProvider\ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeader'],
                    'httpHeaderPrefix' => TokenProvider\ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeaderPrefix'],
                ]],
                [TokenProvider\ServerRequestCookieProvider::class => [
                    'cookieName' => TokenProvider\ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName']
                ]]
            ];
        }

        return new JwtAuthMiddleware(
            $tokenProviders,
            $this->getDefaultJwtService()
        );
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

    /**
     * @overrides parent method to add phpdoc DelegateInterface for phpstan
     *
     * @param string $originalClassName
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|DelegateInterface
     *
     * @throws \Exception
     */
    protected function createMock($originalClassName)
    {
        return parent::createMock($originalClassName);
    }
}
