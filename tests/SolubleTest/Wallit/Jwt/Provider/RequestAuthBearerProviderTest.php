<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Jwt\Provider;

use PHPUnit\Framework\TestCase;
use Soluble\Wallit\Jwt\Provider\RequestAuthBearerProvider;
use Zend\Diactoros\ServerRequest;

class RequestAuthBearerProviderTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testValidToken()
    {
        $rawToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';

        $request = (new ServerRequest())->withAddedHeader('Authentication', "Bearer $rawToken");

        $storage = new RequestAuthBearerProvider($request);

        $this->assertTrue($storage->hasToken());
        $this->assertEquals($rawToken, $storage->getTokenString());
    }

    public function testNoToken()
    {
        $storage = new RequestAuthBearerProvider(new ServerRequest());
        $this->assertFalse($storage->hasToken());
    }

    public function testMultipleTokens()
    {
        $request = (new ServerRequest())
                    ->withAddedHeader('Authentication', '_Notabearer_')
                    ->withAddedHeader('Authentication', 'Bearer _firstbearer_')
                    ->withAddedHeader('Authentication', 'Bearer _secondbearer_')
                    ->withAddedHeader('Authentication', 'Bearer_invalid');

        $storage = new RequestAuthBearerProvider($request);

        $this->assertTrue($storage->hasToken());
        $this->assertEquals('_secondbearer_', $storage->getTokenString());
    }

    public function testGetTokenStringNoTokenReturnNull()
    {
        $storage = new RequestAuthBearerProvider(new ServerRequest());
        $this->assertNull($storage->getTokenString());
    }
}
