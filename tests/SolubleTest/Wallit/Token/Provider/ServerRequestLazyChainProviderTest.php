<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Token\Provider;

use PHPUnit\Framework\TestCase;
use Soluble\Wallit\Token\Provider\ServerRequestAuthBearerProvider;
use Soluble\Wallit\Token\Provider\ServerRequestCookieProvider;
use Soluble\Wallit\Token\Provider\ServerRequestLazyChainProvider;
use Zend\Diactoros\ServerRequest;

class ServerRequestLazyChainProviderTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testConstructWithNoProviderThrowsInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);

        new ServerRequestLazyChainProvider(new ServerRequest(), [
        ]);
    }

    public function testProvidersAsArrayHeaderFirst()
    {
        $cookieName = ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName'];

        $request = (new ServerRequest())
                        ->withCookieParams([$cookieName => 'cookie_token'])
                        ->withAddedHeader('Authentication', 'Bearer header_token');

        $tokenProvider = new ServerRequestLazyChainProvider($request, [
            [ServerRequestAuthBearerProvider::class => [
                'httpHeader' => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeader'],
                'httpHeaderPrefix' => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeaderPrefix'],
            ]],
            [ServerRequestCookieProvider::class => [
                'cookieName' => $cookieName
            ]]
        ]);

        $plainToken = $tokenProvider->getPlainToken();
        $this->assertEquals('header_token', $plainToken);
        $this->assertTrue($tokenProvider->hasToken());
    }

    public function testProvidersAsArrayCookieFirst()
    {
        $cookieName = ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName'];

        $request = (new ServerRequest())
            ->withCookieParams([$cookieName => 'cookie_token'])
            ->withAddedHeader('Authentication', 'Bearer header_token');

        $tokenProvider = new ServerRequestLazyChainProvider($request, [
            [ServerRequestCookieProvider::class => [
                'cookieName' => $cookieName
            ]],
            [ServerRequestAuthBearerProvider::class => [
                'httpHeader' => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeader'],
                'httpHeaderPrefix' => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeaderPrefix'],
            ]]
        ]);

        $plainToken = $tokenProvider->getPlainToken();
        $this->assertEquals('cookie_token', $plainToken);
        $this->assertTrue($tokenProvider->hasToken());
    }

    public function testProvidersAsObject()
    {
        $cookieName = ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName'];

        $request = (new ServerRequest())
            ->withCookieParams([$cookieName => 'cookie_token'])
            ->withAddedHeader('Authentication', 'Bearer header_token');

        $tokenProvider = new ServerRequestLazyChainProvider($request, [
            new ServerRequestAuthBearerProvider($request, [
                'httpHeader' => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeader'],
                'httpHeaderPrefix' => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeaderPrefix'],
            ]),
            new ServerRequestCookieProvider($request, [
                'cookieName' => $cookieName
            ])
        ]);

        $plainToken = $tokenProvider->getPlainToken();
        $this->assertEquals('header_token', $plainToken);
        $this->assertTrue($tokenProvider->hasToken());
    }

    public function testGetPlainTokenThrowsInvalidArgumentWhenWrongInterface()
    {
        $this->expectException(\InvalidArgumentException::class);
        $request = (new ServerRequest());
        (new ServerRequestLazyChainProvider($request, [
            new \stdClass()
        ]))->getPlainToken();
    }

    public function testGetPlainTokenThrowsInvalidArgumentWhenWrongClassname()
    {
        $this->expectException(\InvalidArgumentException::class);
        $request = (new ServerRequest());
        (new ServerRequestLazyChainProvider($request, [
            '\Namespace\Invalid\Class'
        ]))->getPlainToken();
    }
}
