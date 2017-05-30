<?php

declare(strict_types=1);

namespace Soluble\Wallit\Service;

use Psr\Container\ContainerInterface;
use Soluble\Wallit\Config\ConfigProvider;
use Soluble\Wallit\Exception\ConfigException;
use Soluble\Wallit\Token\Jwt\SignatureAlgos;

class JwtServiceFactory
{
    public const CONFIG_KEY = 'token_service';

    /**
     * Map signature algorithms.
     *
     * @var array
     */
    public static $algosMap = [
        SignatureAlgos::HS256 => \Lcobucci\JWT\Signer\Hmac\Sha256::class,
        SignatureAlgos::HS384 => \Lcobucci\JWT\Signer\Hmac\Sha384::class,
        SignatureAlgos::HS512 => \Lcobucci\JWT\Signer\Hmac\Sha512::class
    ];

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
                    "Missing or invalid entry ['%s']['%s'] in container configuration.",
                    ConfigProvider::CONFIG_PREFIX,
                    self::CONFIG_KEY)
            );
        }

        $verificationKey = $options['secret'] ?? null;
        if ($verificationKey === null) {
            throw new ConfigException(sprintf(
                "Missing secret key in config (['%s']['%s']['%s'] = '%s')",
                ConfigProvider::CONFIG_PREFIX,
                self::CONFIG_KEY,
                'secret',
                $verificationKey ?? 'NULL'
            ));
        }

        $algo = $options['algo'] ?? null;
        $algoClass = self::$algosMap[$algo] ?? null;

        if ($algoClass === null || !class_exists($algoClass)) {
            throw new ConfigException(sprintf(
                "Missing or invalid algorithm in config (['%s']['%s']['%s'] = '%s')",
                ConfigProvider::CONFIG_PREFIX,
                self::CONFIG_KEY,
                'algo',
                $algo ?? 'NULL'
            ));
        }

        $jwtService = new JwtService(new $algoClass(), $verificationKey);

        return $jwtService;
    }
}
