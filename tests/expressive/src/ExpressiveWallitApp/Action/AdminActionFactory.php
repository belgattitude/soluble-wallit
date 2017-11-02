<?php

declare(strict_types=1);

namespace ExpressiveWallitApp\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class AdminActionFactory
{
    public function __invoke(ContainerInterface $container): AdminAction
    {
        return new AdminAction(
            $container->get(TemplateRendererInterface::class)
        );
    }
}
