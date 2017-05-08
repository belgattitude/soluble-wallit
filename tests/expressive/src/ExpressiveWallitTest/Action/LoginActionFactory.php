<?php

declare(strict_types=1);

namespace ExpressiveWallitTest\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class LoginActionFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return LoginAction
     */
    public function __invoke(ContainerInterface $container): LoginAction
    {
        return new LoginAction(
            $container->get(TemplateRendererInterface::class)
        );
    }
}
