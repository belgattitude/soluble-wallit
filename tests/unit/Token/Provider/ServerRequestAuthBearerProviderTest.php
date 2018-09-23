<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Token\Provider;

use PHPUnit\Framework\TestCase;
use Soluble\Wallit\Token\Provider\ServerRequestAuthBearerProvider;
use Zend\Diactoros\ServerRequest;

class ServerRequestAuthBearerProviderTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testConstructThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ServerRequestAuthBearerProvider(new ServerRequest(), [
            'httpHeader'                                               => '',
            ServerRequestAuthBearerProvider::OPTION_HTTP_HEADER_PREFIX => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeaderPrefix']
        ]);
    }

    public function testValidToken(): void
    {
        $rawToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';

        $request = (new ServerRequest())->withAddedHeader('Authentication', "Bearer $rawToken");

        $provider = new ServerRequestAuthBearerProvider($request);

        self::assertTrue($provider->hasToken());

        self::assertEquals($rawToken, $provider->getPlainToken());
    }

    public function testValidTokenWithNoPrefix(): void
    {
        $rawToken = 'my_token';

        $request = (new ServerRequest())->withAddedHeader('Authentication', $rawToken);

        $provider = new ServerRequestAuthBearerProvider($request, [
            ServerRequestAuthBearerProvider::OPTION_HTTP_HEADER_PREFIX => ''
        ]);

        self::assertTrue($provider->hasToken());

        self::assertEquals($rawToken, $provider->getPlainToken());
    }

    public function testHasTokenFalseWhenNoHeader(): void
    {
        $provider = new ServerRequestAuthBearerProvider(new ServerRequest());
        self::assertFalse($provider->hasToken());
    }

    public function testHasTokenFalseWhenInvalidHeader(): void
    {
        $request = (new ServerRequest())
            ->withAddedHeader('Authentication', '_Notabearer_')
            ->withAddedHeader('Authentication', 'Bearer_invalid');

        $provider = new ServerRequestAuthBearerProvider($request);

        self::assertFalse($provider->hasToken());
    }

    public function testMultipleTokens(): void
    {
        $request = (new ServerRequest())
                    ->withAddedHeader('Authentication', '_Notabearer_')
                    ->withAddedHeader('Authentication', 'Bearer _firstbearer_')
                    ->withAddedHeader('Authentication', 'Bearer _secondbearer_')
                    ->withAddedHeader('Authentication', 'Bearer_invalid');

        $provider = new ServerRequestAuthBearerProvider($request);

        self::assertTrue($provider->hasToken());
        self::assertEquals('_secondbearer_', $provider->getPlainToken());
    }

    public function testGetTokenStringNoTokenReturnNull(): void
    {
        $provider = new ServerRequestAuthBearerProvider(new ServerRequest());
        self::assertNull($provider->getPlainToken());
    }
}
