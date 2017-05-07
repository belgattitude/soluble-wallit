<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Jwt\Provider;

use PHPUnit\Framework\TestCase;
use Soluble\Wallit\Jwt\Provider\RequestCookieProvider;
use Zend\Diactoros\ServerRequest;

class RequestCookieProviderTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testValidToken()
    {
        $rawToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';

        $cookieName = 'cookie_name_for_token_exists';

        $request = (new ServerRequest())->withCookieParams([
            $cookieName => $rawToken
        ]);
        $storage = new RequestCookieProvider($request, $cookieName);

        $this->assertEquals($rawToken, $storage->getTokenString());
        $this->assertTrue($storage->hasToken());
    }

    public function testNoToken()
    {
        $storage = new RequestCookieProvider(new ServerRequest(), 'cookie_name_for_token_empty');
        $this->assertFalse($storage->hasToken());
    }

    public function testGetTokenStringNoTokenReturnNull()
    {
        $storage = new RequestCookieProvider(new ServerRequest(), 'cookie_name_for_token_empty');
        $this->assertNull($storage->getTokenString());
    }
}
