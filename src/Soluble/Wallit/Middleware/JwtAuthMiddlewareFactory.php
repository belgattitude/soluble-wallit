<?php

declare(strict_types=1);

namespace Soluble\Wallit\Middleware;

use Psr\Container\ContainerInterface;
use Soluble\Wallit\Exception\ConfigException;
use Soluble\Wallit\Service\JwtService;

class JwtAuthMiddlewareFactory
{
    public const CONFIG_KEY = 'token-auth-middleware';

    /**
     * @param ContainerInterface $container
     *
     * @return JwtAuthMiddleware
     */
    public function __invoke(ContainerInterface $container): JwtAuthMiddleware
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $options = $config[self::CONFIG_KEY] ?? null;

        if (!is_array($options)) {
            throw new ConfigException(sprintf(
                    "Missing or invalid '%s' entry in container configuration (config)",
                    self::CONFIG_KEY)
            );
        }

        if (!$container->has(JwtService::class)) {
            throw new ConfigException(sprintf(
                    "Cannot locate '%s' from container, was it provided?",
                    JwtService::class)
            );
        }

        return new JwtAuthMiddleware($container->get(JwtService::class), $options);
    }
}
