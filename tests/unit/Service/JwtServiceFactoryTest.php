<?php

namespace SolubleTest\Wallit\Service;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Soluble\Wallit\Config\ConfigProvider;
use Soluble\Wallit\Exception\ConfigException;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Service\JwtServiceFactory;
use Soluble\Wallit\Token\Jwt\SignatureAlgos;

class JwtServiceFactoryTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactorySuccess(): void
    {
        $factory = new JwtServiceFactory();
        $this->container->has('config')->willReturn(true);
        $this->container
            ->get('config')
            ->willReturn([
                ConfigProvider::CONFIG_PREFIX => [
                    JwtServiceFactory::CONFIG_KEY => [
                        'algo'   => SignatureAlgos::HS256,
                        'secret' => 'my_secret_signature_key'
                    ]
                ]
            ]);

        $jwtService = $factory($this->container->reveal());
        $this->assertInstanceOf(JwtService::class, $jwtService);
    }

    public function testFactoryThrowsExceptionMissingConfig(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Missing or invalid entry ['%s']['%s'] in container configuration.",
                ConfigProvider::CONFIG_PREFIX,
                JwtServiceFactory::CONFIG_KEY
            )
        );
        $factory = new JwtServiceFactory();
        $factory($this->container->reveal());
    }

    public function testFactoryThrowsExceptionMissingAlgo(): void
    {
        $this->expectException(ConfigException::class);

        $this->expectExceptionMessage(
            sprintf(
                "Missing or invalid algorithm in config (['%s']['%s']['%s'] = '%s')",
                ConfigProvider::CONFIG_PREFIX,
                JwtServiceFactory::CONFIG_KEY,
                'algo',
                'NULL'
            )
        );

        $factory = new JwtServiceFactory();
        $this->container->has('config')->willReturn(true);
        $this->container
            ->get('config')
            ->willReturn([
                ConfigProvider::CONFIG_PREFIX => [
                    JwtServiceFactory::CONFIG_KEY => [
                        'secret' => 'my_secret_signature_key'
                    ]
                ]
            ]);

        $factory($this->container->reveal());
    }

    public function testFactoryThrowsExceptionInvalidAlgo(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Missing or invalid algorithm in config (['%s']['%s']['%s'] = '%s')",
                ConfigProvider::CONFIG_PREFIX,
                JwtServiceFactory::CONFIG_KEY,
                'algo',
                'NONE'
            )
        );

        $factory = new JwtServiceFactory();
        $this->container->has('config')->willReturn(true);
        $this->container
            ->get('config')
            ->willReturn([
                ConfigProvider::CONFIG_PREFIX => [
                    JwtServiceFactory::CONFIG_KEY => [
                        'algo'   => 'NONE',
                        'secret' => 'my_secret_signature_key'
                    ]
                ]
            ]);

        $factory($this->container->reveal());
    }

    public function testFactoryThrowsExceptionMissingSecret(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Missing secret key in config (['%s']['%s']['%s'] = '%s')",
                ConfigProvider::CONFIG_PREFIX,
                JwtServiceFactory::CONFIG_KEY,
                'secret',
                'NULL'
            )
        );

        $factory = new JwtServiceFactory();
        $this->container->has('config')->willReturn(true);
        $this->container
            ->get('config')
            ->willReturn([
                ConfigProvider::CONFIG_PREFIX => [
                    JwtServiceFactory::CONFIG_KEY => [
                        'algo'   => 'NONE',
                    ]
                ]
            ]);

        $factory($this->container->reveal());
    }

    public function testFactoryThrowsExceptionEmptySecret(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $factory = new JwtServiceFactory();
        $this->container->has('config')->willReturn(true);
        $this->container
            ->get('config')
            ->willReturn([
                ConfigProvider::CONFIG_PREFIX => [
                    JwtServiceFactory::CONFIG_KEY => [
                        'algo'   => SignatureAlgos::HS256,
                        'secret' => ''
                    ]
                ]
            ]);

        $factory($this->container->reveal());
    }
}
