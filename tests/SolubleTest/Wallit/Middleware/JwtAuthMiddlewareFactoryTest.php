<?php

namespace SolubleTest\Wallit\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Soluble\Wallit\Middleware\JwtAuthMiddlewareFactory;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;

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
        /*
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));
        */

        $this->assertInstanceOf(JwtAuthMiddlewareFactory::class, $factory);
        $jwtMiddleware = $factory($this->container->reveal());

        $this->assertInstanceOf(JwtAuthMiddleware::class, $jwtMiddleware);
    }
}
