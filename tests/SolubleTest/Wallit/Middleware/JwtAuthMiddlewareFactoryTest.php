<?php

namespace SolubleTest\Wallit\Middleware;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Soluble\Wallit\Config\ConfigProvider;
use Soluble\Wallit\Exception\ConfigException;
use Soluble\Wallit\Middleware\JwtAuthMiddlewareFactory;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Token\Provider as TokenProvider;

class JwtAuthMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testBasicFactoryTest(): void
    {
        $factory = new JwtAuthMiddlewareFactory();

        $this->container
            ->has(JwtService::class)->willReturn(true);
        $this->container
            ->get(JwtService::class)->willReturn(
                new JwtService(new Sha256(), 'private-key')
            );

        $this->container
            ->has('config')->willReturn(true);
        $this->container
            ->get('config')
            ->willReturn([
                ConfigProvider::CONFIG_PREFIX => [
                    JwtAuthMiddlewareFactory::CONFIG_KEY => [
                        'token-providers' => [
                            [TokenProvider\ServerRequestAuthBearerProvider::class => [
                                'httpHeader'       => TokenProvider\ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeader'],
                                'httpHeaderPrefix' => TokenProvider\ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeaderPrefix'],
                            ]]
                        ]
                    ]
                ]
            ]);

        $jwtMiddleware = $factory($this->container->reveal());
        $this->assertInstanceOf(JwtAuthMiddleware::class, $jwtMiddleware);
    }

    public function testFactoryThrowsExceptionNoConfig(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(sprintf(
            "Missing or invalid entry ['%s']['%s'] in container configuration.",
            ConfigProvider::CONFIG_PREFIX,
            JwtAuthMiddlewareFactory::CONFIG_KEY));
        $factory = new JwtAuthMiddlewareFactory();
        $factory($this->container->reveal());
    }

    public function testFactoryThrowsExceptionWrongService(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(sprintf(
                "Cannot locate required '%s' from container, was it provided ?",
                JwtService::class)
        );

        $factory = new JwtAuthMiddlewareFactory();
        $this->container->has('config')->willReturn(true);

        $this->container->has(JwtService::class)->willReturn(false);

        $this->container
            ->get('config')
            ->willReturn([
                ConfigProvider::CONFIG_PREFIX => [
                    JwtAuthMiddlewareFactory::CONFIG_KEY => [
                        'token-providers' => []
                    ]
                ]
            ]);

        $factory($this->container->reveal());
    }

    public function testFactoryThrowsExceptionWrongTokenProviders(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(sprintf(
            "Missing or invalid entry ['%s']['%s']['%s'] in container configuration.",
            ConfigProvider::CONFIG_PREFIX,
            JwtAuthMiddlewareFactory::CONFIG_KEY,
            'token-providers')
        );

        $factory = new JwtAuthMiddlewareFactory();
        $this->container->has('config')->willReturn(true);
        $this->container->has(JwtService::class)
                        ->willReturn(
                            new JwtService(new Sha256(), 'private-key')
                        );

        $this->container
            ->get('config')
            ->willReturn([
                ConfigProvider::CONFIG_PREFIX => [
                    JwtAuthMiddlewareFactory::CONFIG_KEY => [
                        // Invalid
                        'token-providers' => false
                    ]
                ]
            ]);

        $factory($this->container->reveal());
    }
}
