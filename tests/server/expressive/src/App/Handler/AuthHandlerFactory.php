<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Soluble\Wallit\Service\JwtService;

class AuthHandlerFactory
{
    public function __invoke(ContainerInterface $container): AuthHandler
    {
        return new AuthHandler(
            $container->get(JwtService::class)
        );
    }
}
