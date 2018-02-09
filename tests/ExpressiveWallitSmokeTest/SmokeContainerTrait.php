<?php

declare(strict_types=1);

namespace ExpressiveWallitSmokeTest;

use Psr\Container\ContainerInterface;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Service\JwtServiceFactory;
use Zend\ServiceManager\ServiceManager;

trait SmokeContainerTrait
{
    public function getContainer(): ContainerInterface
    {
        $config = require __DIR__ . '/../server/expressive/config/autoload/soluble-wallit.local.php';
        $container = new ServiceManager();
        $container->setService('config', $config);

        return $container;
    }

    public function getJwtService(): JwtService
    {
        return (new JwtServiceFactory())->__invoke($this->getContainer());
    }
}
