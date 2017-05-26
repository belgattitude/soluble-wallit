<?php

namespace SolubleTest\Wallit\Service;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Soluble\Wallit\Exception\ConfigException;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Service\JwtServiceFactory;

class JwtServiceFactoryTest extends TestCase
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
        $factory = new JwtServiceFactory();
        $factory($this->container->reveal());
    }

    public function testFactorySuccess(): void
    {
        $factory = new JwtServiceFactory();
        $this->container
            ->has('config')->willReturn(true);

        $this->container
            ->get('config')
            ->willReturn([
                    JwtServiceFactory::CONFIG_KEY => [
                    ]
            ]);

        $jwtService = $factory($this->container->reveal());
        $this->assertInstanceOf(JwtService::class, $jwtService);
    }
}
