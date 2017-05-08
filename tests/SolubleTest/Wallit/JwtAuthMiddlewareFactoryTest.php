<?php

namespace SolubleTest\Wallit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Soluble\Wallit\JwtAuthMiddlewareFactory;
use Soluble\Wallit\JwtAuthMiddleware;

class JwtAuthMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testBasicFactoryTest()
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
