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
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertArrayHasKey('factories', $config['dependencies']);

        $factories = $config['dependencies']['factories'];
        $this->assertArrayHasKey(JwtAuthMiddleware::class, $factories);
    }

    public function testGetDependencies(): void
    {
        $deps = (new ConfigProvider())->getDependencies();
        $this->assertArrayHasKey('factories', $deps);

        $factories = $deps['factories'];
        $this->assertArrayHasKey(JwtAuthMiddleware::class, $factories);
    }
}
