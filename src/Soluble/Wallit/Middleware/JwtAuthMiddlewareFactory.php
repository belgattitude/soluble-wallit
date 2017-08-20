<?php

declare(strict_types=1);

namespace Soluble\Wallit\Middleware;

use Psr\Container\ContainerInterface;
use Soluble\Wallit\Config\ConfigProvider;
use Soluble\Wallit\Exception\ConfigException;
use Soluble\Wallit\Service\JwtService;

class JwtAuthMiddlewareFactory
{
    public const CONFIG_KEY = 'token_auth_middleware';

    /**
     * @param ContainerInterface $container
     *
     * @return JwtAuthMiddleware
     */
    public function __invoke(ContainerInterface $container): JwtAuthMiddleware
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $options = $config[ConfigProvider::CONFIG_PREFIX][self::CONFIG_KEY] ?? false;

        if (!is_array($options)) {
            throw new ConfigException(
                sprintf(
                    "Missing or invalid entry ['%s']['%s'] in container configuration.",
                    ConfigProvider::CONFIG_PREFIX,
                    self::CONFIG_KEY
            )
            );
        }

        if (!$container->has(JwtService::class)) {
            throw new ConfigException(
                sprintf(
                    "Cannot locate required '%s' from container, was it provided ?",
                    JwtService::class
            )
            );
        }

        if (!isset($options['token_providers']) || !is_array($options['token_providers'])) {
            throw new ConfigException(
                sprintf(
                    "Missing or invalid entry ['%s']['%s']['%s'] in container configuration.",
                    ConfigProvider::CONFIG_PREFIX,
                    self::CONFIG_KEY,
                    'token_providers'
            )
            );
        }

        return new JwtAuthMiddleware(
            $options['token_providers'],
                                     $container->get(JwtService::class)
        );
    }
}
