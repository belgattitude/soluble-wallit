<?php

declare(strict_types=1);

namespace Soluble\Wallit\Service;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Container\ContainerInterface;

class JwtServiceFactory
{
    private const CONFIG_KEY = 'soluble-wallit';

    /**
     * @param ContainerInterface $container
     *
     * @return JwtService
     *
     * @throws \RuntimeException
     */
    public function __invoke(ContainerInterface $container): JwtService
    {
        $config = $container->has('config') ? $container->get('config') : [];

        $wallitConfig = $config[self::CONFIG_KEY] ?? null;

        if (!is_array($wallitConfig)) {
            throw new \RuntimeException(sprintf(
                "Missing or invalid '%s' entry in container configuration (config)",
                self::CONFIG_KEY)
            );
        }

        $jwtService = new JwtService(new Sha256(), 'private-key');

        return $jwtService;
    }
}
