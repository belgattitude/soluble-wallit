<?php

declare(strict_types=1);

namespace ExpressiveWallitApp\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class AdminHandlerFactory
{
    public function __invoke(ContainerInterface $container): AdminHandler
    {
        return new AdminHandler(
            $container->get(TemplateRendererInterface::class)
        );
    }
}
