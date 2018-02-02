<?php

declare(strict_types=1);

namespace ExpressiveWallitApp\Action;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class LoginActionFactory
{
    public function __invoke(ContainerInterface $container): LoginAction
    {
        return new LoginAction(
            $container->get(TemplateRendererInterface::class)
        );
    }
}
