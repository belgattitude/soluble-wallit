<?php

declare(strict_types=1);

namespace Soluble\Wallit\Expressive;

use Soluble\Wallit\JwtAuthMiddleware;
use Soluble\Wallit\JwtAuthMiddlewareFactory;

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
