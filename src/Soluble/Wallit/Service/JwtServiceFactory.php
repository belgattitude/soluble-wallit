<?php

declare(strict_types=1);

namespace Soluble\Wallit\Service;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Container\ContainerInterface;
use Soluble\Wallit\Exception\ConfigException;

class JwtServiceFactory
{
    public const CONFIG_KEY = 'soluble-wallit-token-service';

    /**
     * @param ContainerInterface $container
     *
     * @return JwtService
     *
     * @throws ConfigException
     */
    public function __invoke(ContainerInterface $container): JwtService
    {
        $config = $container->has('config') ? $container->get('config') : [];

        $wallitConfig = $config[self::CONFIG_KEY] ?? null;

        if (!is_array($wallitConfig)) {
            throw new ConfigException(sprintf(
                "Missing or invalid '%s' entry in container configuration (config)",
                self::CONFIG_KEY)
            );
        }

        $jwtService = new JwtService(new Sha256(), 'private-key');

        return $jwtService;
    }
}
