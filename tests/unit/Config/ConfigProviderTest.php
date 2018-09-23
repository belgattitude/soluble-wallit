<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Config;

use PHPUnit\Framework\TestCase;
use Soluble\Wallit\Config\ConfigProvider;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;

class ConfigProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $config = (new ConfigProvider())->__invoke();
        self::assertArrayHasKey('dependencies', $config);
        self::assertArrayHasKey('factories', $config['dependencies']);

        $factories = $config['dependencies']['factories'];
        self::assertArrayHasKey(JwtAuthMiddleware::class, $factories);
    }

    public function testGetDependencies(): void
    {
        $deps = (new ConfigProvider())->getDependencies();
        self::assertArrayHasKey('factories', $deps);

        $factories = $deps['factories'];
        self::assertArrayHasKey(JwtAuthMiddleware::class, $factories);
    }
}
