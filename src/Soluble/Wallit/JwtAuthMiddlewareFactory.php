<?php

declare(strict_types=1);

namespace Soluble\Wallit;

use Interop\Container\ContainerInterface;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Soluble\Wallit\Service\JwtService;

class JwtAuthMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return JwtAuthMiddleware
     */
    public function __invoke(ContainerInterface $container): JwtAuthMiddleware
    {
        $options = [
            'secure' => true, // Check for https
            'relaxed' => [],
            'signer' => new Sha256(),
            'privateKey' => 'private-key', // my super secret key
            /*
            'jwtStorage' => [
                HttpCookieStorage::class => [
                    'name' => 'jwtcookie',
                    'path' => '/',
                    //"httponly" => true,
                    //"secure" => true
                ],
                HttpAuthBearerStorage::class
            ],*/
            'attribute' => JwtAuthMiddleware::class, // request attribute
        ];

        $jwtService = new JwtService(new Sha256(), 'private-key');

        return new JwtAuthMiddleware($jwtService);
    }
}
