<?php

declare(strict_types=1);

namespace ExpressiveWallitApp\Action;

use Interop\Container\ContainerInterface;
use Soluble\Wallit\Service\JwtService;

class AuthActionFactory
{
    public function __invoke(ContainerInterface $container): AuthAction
    {
        return new AuthAction(
            $container->get(JwtService::class)
        );
    }
}
