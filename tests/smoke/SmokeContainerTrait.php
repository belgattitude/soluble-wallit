<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Smoke;

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Service\JwtServiceFactory;
use Zend\ServiceManager\ServiceManager;

trait SmokeContainerTrait
{
    public function getClient(): Client
    {
        return new Client([
            'base_uri' => sprintf('http://%s:%s', EXPRESSIVE_SERVER_HOST, EXPRESSIVE_SERVER_PORT),
            'timeout'  => 5,
        ]);
    }

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
