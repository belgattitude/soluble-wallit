<?php

declare(strict_types=1);

namespace Soluble\JwtAuth\Expressive;

use Soluble\JwtAuth\JwtAuthMiddleware;
use Soluble\JwtAuth\JwtAuthMiddlewareFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies()
        ];
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                JwtAuthMiddleware::class => JwtAuthMiddlewareFactory::class,
            ],
        ];
    }
}
