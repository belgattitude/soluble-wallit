<?php

declare(strict_types=1);

namespace Soluble\Wallit\Expressive;

use Soluble\Wallit\Middleware\JwtAuthMiddleware;
use Soluble\Wallit\Middleware\JwtAuthMiddlewareFactory;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Service\JwtServiceFactory;

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
                JwtAuthMiddleware::class     => JwtAuthMiddlewareFactory::class,
                JwtService::class            => JwtServiceFactory::class
            ],
        ];
    }
}
