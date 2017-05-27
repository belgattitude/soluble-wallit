<?php

declare(strict_types=1);

namespace Soluble\Wallit\Service;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Container\ContainerInterface;
use Soluble\Wallit\Config\ConfigProvider;
use Soluble\Wallit\Exception\ConfigException;

class JwtServiceFactory
{
    public const CONFIG_KEY = 'token-service';

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

        $options = $config[ConfigProvider::CONFIG_PREFIX][self::CONFIG_KEY] ?? null;

        if (!is_array($options)) {
            throw new ConfigException(sprintf(
                    "Missing or invalid ['%s']['%s'] entry in container configuration (config)",
                    ConfigProvider::CONFIG_PREFIX,
                    self::CONFIG_KEY)
            );
        }

        $jwtService = new JwtService(new Sha256(), 'private-key');

        return $jwtService;
    }
}
