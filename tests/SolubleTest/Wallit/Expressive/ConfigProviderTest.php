<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Expressive;

use PHPUnit\Framework\TestCase;
use Soluble\Wallit\Expressive\ConfigProvider;
use Soluble\Wallit\JwtAuthMiddleware;

class ConfigProviderTest extends TestCase
{
    public function testProvider()
    {
        $config = (new ConfigProvider())();
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertArrayHasKey('factories', $config['dependencies']);

        $factories = $config['dependencies']['factories'];
        $this->assertArrayHasKey(JwtAuthMiddleware::class, $factories);
    }
}
