<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Jwt\Provider;

use PHPUnit\Framework\TestCase;
use Soluble\Wallit\Token\Provider\ServerRequestAuthBearerProvider;
use Zend\Diactoros\ServerRequest;

class ServerRequestAuthBearerProviderTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testConstructThrowsInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new ServerRequestAuthBearerProvider(new ServerRequest(), [
            'httpHeader' => '',
            'httpHeaderPrefix' => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeaderPrefix']
        ]);
    }

    public function testValidToken()
    {
        $rawToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';

        $request = (new ServerRequest())->withAddedHeader('Authentication', "Bearer $rawToken");

        $provider = new ServerRequestAuthBearerProvider($request);

        $this->assertTrue($provider->hasToken());

        $this->assertEquals($rawToken, $provider->getPlainToken());
    }

    public function testHasTokenFalseWhenNoHeader()
    {
        $provider = new ServerRequestAuthBearerProvider(new ServerRequest());
        $this->assertFalse($provider->hasToken());
    }

    public function testHasTokenFalseWhenInvalidHeader()
    {
        $request = (new ServerRequest())
            ->withAddedHeader('Authentication', '_Notabearer_')
            ->withAddedHeader('Authentication', 'Bearer_invalid');

        $provider = new ServerRequestAuthBearerProvider($request);

        $this->assertFalse($provider->hasToken());
    }

    public function testMultipleTokens()
    {
        $request = (new ServerRequest())
                    ->withAddedHeader('Authentication', '_Notabearer_')
                    ->withAddedHeader('Authentication', 'Bearer _firstbearer_')
                    ->withAddedHeader('Authentication', 'Bearer _secondbearer_')
                    ->withAddedHeader('Authentication', 'Bearer_invalid');

        $provider = new ServerRequestAuthBearerProvider($request);

        $this->assertTrue($provider->hasToken());
        $this->assertEquals('_secondbearer_', $provider->getPlainToken());
    }

    public function testGetTokenStringNoTokenReturnNull()
    {
        $provider = new ServerRequestAuthBearerProvider(new ServerRequest());
        $this->assertNull($provider->getPlainToken());
    }
}
