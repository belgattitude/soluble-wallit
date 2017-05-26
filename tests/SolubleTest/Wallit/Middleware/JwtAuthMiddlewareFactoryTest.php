<?php

namespace SolubleTest\Wallit\Middleware;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Soluble\Wallit\Exception\ConfigException;
use Soluble\Wallit\Middleware\JwtAuthMiddlewareFactory;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;
use Soluble\Wallit\Service\JwtService;

class JwtAuthMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryThrowsException(): void
    {
        $this->expectException(ConfigException::class);
        $factory = new JwtAuthMiddlewareFactory();
        $factory($this->container->reveal());
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
                JwtAuthMiddlewareFactory::CONFIG_KEY => [
                ]
            ]);

        $jwtMiddleware = $factory($this->container->reveal());

        $this->assertInstanceOf(JwtAuthMiddleware::class, $jwtMiddleware);
    }
}
