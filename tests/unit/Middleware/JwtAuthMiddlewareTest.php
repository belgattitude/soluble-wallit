<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Middleware;

use Psr\Http\Server\RequestHandlerInterface;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\Wallit\Middleware\Exception\InsecureSchemeException;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Token\Provider as TokenProvider;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class JwtAuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testAuthTokenFromCookie(): void
    {
        $jwtMw = $this->buildJwtAuthMiddleware();

        $token = $this->getDefaultJwtService()->createToken(['uid' => 10]);

        $handler = $this->getMockedDelegate(function (ServerRequestInterface $request) {
            $data = $request->getAttribute(JwtAuthMiddleware::class);
            self::assertInstanceOf(Token::class, $data);

            return (new Response())->withAddedHeader('test', 'passed');
        });

        $cookieName = TokenProvider\ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName'];
        $response = $jwtMw->process(
            (new ServerRequest())
                ->withCookieParams(
                    [$cookieName => (string) $token]
            ),
            $handler
        );

        self::assertContains('passed', $response->getHeader('test'));
    }

    public function testAuthTokenFromAuthenticationHeader(): void
    {
        $jwtMw = $this->buildJwtAuthMiddleware();

        $token = $this->getDefaultJwtService()->createToken(['uid' => 10]);

        $handler = $this->getMockedDelegate(function (ServerRequestInterface $request) {
            $data = $request->getAttribute(JwtAuthMiddleware::class);
            self::assertInstanceOf(Token::class, $data);

            return (new Response())->withAddedHeader('test', 'passed');
        });

        $response = $jwtMw->process(
            (new ServerRequest())
                ->withAddedHeader('Authentication', "Bearer $token"),
            $handler
        );

        self::assertContains('passed', $response->getHeader('test'));
    }

    public function testNotParseableTokenFromAuthenticationHeader(): void
    {
        $jwtMw = $this->buildJwtAuthMiddleware();

        $token = 'aninvalidToken';

        /* For PHPSTAN
         * @var DelegateInterface|\PHPUnit_Framework_MockObject_MockObject $handler
         */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $response = $jwtMw->process(
            (new ServerRequest())
                ->withAddedHeader('Authentication', "Bearer $token"),
            $handler
        );

        self::assertEquals(401, $response->getStatusCode());

        self::assertInstanceOf(Response\JsonResponse::class, $response);
    }

    public function testInvalidSignatureToken(): void
    {
        $jwtMw = $this->buildJwtAuthMiddleware();

        $tokenString = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';

        $token = $this->getDefaultJwtService()->parsePlainToken($tokenString);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $cookieName = TokenProvider\ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName'];
        $response = $jwtMw->process(
            (new ServerRequest())
                ->withCookieParams(
                    [$cookieName => (string) $token]
                ),
            $handler
        );

        self::assertEquals(401, $response->getStatusCode());
        self::assertInstanceOf(Response\JsonResponse::class, $response);
        self::assertContains('invalid', json_decode($response->getBody()->getContents())->reason);
    }

    public function testNoToken(): void
    {
        $jwtMw = $this->buildJwtAuthMiddleware();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $response = $jwtMw->process(new ServerRequest(), $handler);

        self::assertEquals(401, $response->getStatusCode());
        self::assertInstanceOf(Response\JsonResponse::class, $response);
        self::assertContains('No token', json_decode($response->getBody()->getContents())->reason);
    }

    public function testExpiredTokenFromCookieHeader(): void
    {
        $jwtMw = $this->buildJwtAuthMiddleware();
        $expiration = new \DateTimeImmutable('-1 day');

        $token = $this->getDefaultJwtService()->createToken(['uid' => 10], $expiration->getTimestamp());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $cookieName = TokenProvider\ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName'];
        $response = $jwtMw->process(
            (new ServerRequest())
                ->withCookieParams(
                    [$cookieName => (string) $token]
                ),
            $handler
        );

        self::assertEquals(401, $response->getStatusCode());
        self::assertInstanceOf(Response\JsonResponse::class, $response);
        self::assertContains('expired', json_decode($response->getBody()->getContents())->reason);
    }

    public function testMiddlewareThrowsExceptionWhenNonHttps(): void
    {
        $serverRequest = (new ServerRequest())
                ->withAddedHeader('Authentication', 'Bearer token_for_tests')
                ->withUri(new Uri('http://www.google.com'));

        $this->expectException(InsecureSchemeException::class);
        $this->expectExceptionMessage(sprintf(
            'Insecure scheme (%s) denied by configuration.',
            $serverRequest->getUri()->getScheme()
        ));

        $jwtMw = $this->buildJwtAuthMiddleware(null, [
            'allow_insecure_http' => false,
            'relaxed_hosts'       => []
        ]);

        /* For PHPSTAN
         * @var DelegateInterface|\PHPUnit_Framework_MockObject_MockObject $handler
         */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $jwtMw->process($serverRequest, $handler);
    }

    public function testMiddlewareWorksWhenNonHttpsAndRelaxedHosts(): void
    {
        $token = $this->getDefaultJwtService()->createToken(['uid' => 10]);

        $serverRequest = (new ServerRequest())
            ->withAddedHeader('Authentication', "Bearer $token")
            ->withUri(new Uri('http://localhost/path/'));

        $jwtMw = $this->buildJwtAuthMiddleware(null, [
            'allow_insecure_http' => false,
            'relaxed_hosts'       => ['localhost']
        ]);

        $handler = $this->getMockedDelegate(function (ServerRequestInterface $request) {
            $data = $request->getAttribute(JwtAuthMiddleware::class);
            self::assertInstanceOf(Token::class, $data);

            return (new Response())->withAddedHeader('test', 'passed');
        });

        $response = $jwtMw->process($serverRequest, $handler);

        self::assertContains('passed', $response->getHeader('test'));
    }

    /**
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    private function buildMiddlewareOptions(array $options = []): array
    {
        $options = array_merge(
            [
                // The defaults for tests
                'allow_insecure_http' => true
            ],
            $options
        );

        return $options;
    }

    /**
     * @param mixed[]|null $tokenProviders
     * @param mixed[]      $options
     *
     * @return JwtAuthMiddleware
     */
    private function buildJwtAuthMiddleware(array $tokenProviders = null, array $options = []): JwtAuthMiddleware
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
            $this->getDefaultJwtService(),
            $this->buildMiddlewareOptions($options)
        );
    }

    protected function getDefaultJwtService(): JwtService
    {
        return new JwtService(new Sha256(), 'my-secret-key');
    }

    /**
     * @param callable $callback
     *
     * @return RequestHandlerInterface&\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockedDelegate(callable $callback): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->willReturnCallback($callback)
            ->with(
                self::isInstanceOf(ServerRequestInterface::class)
            );

        return $handler;
    }
}
