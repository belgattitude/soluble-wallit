<?php

declare(strict_types=1);

namespace Soluble\Wallit\Token\Provider;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestLazyChainProvider implements ProviderInterface
{
    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $originalProviders = [];

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * ServerRequestLazyChainProvider constructor.
     *
     * @param ServerRequestInterface $request
     * @param array                  $providers initial providers to lazy load
     */
    public function __construct(ServerRequestInterface $request, array $providers)
    {
        $this->request = $request;
        $this->originalProviders = $providers;
        $this->providers = $providers;
    }

    /**
     * @return bool
     */
    public function hasToken(): bool
    {
        return $this->getPlainToken() !== null;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return null|string
     */
    public function getPlainToken(): ?string
    {
        foreach ($this->providers as $idx => $providerParam) {
            // Lazy load the class
            if (is_string($providerParam) || is_array($providerParam)) {
                if (is_string($providerParam)) {
                    $providerParam = [$providerParam => []];
                }
                $providerClass = key($providerParam);
                $providerOptions = $providerParam[$providerClass];
                if (class_exists($providerClass)) {
                    $provider = new $providerClass($this->request, $providerOptions);
                } else {
                    throw new \InvalidArgumentException(
                        sprintf("Cannot instanciate provider '%s' class cannot be loaded.",
                            is_object($providerClass) ? get_class($providerClass) : gettype($providerClass)
                        )
                    );
                }
            } else {
                $provider = $providerParam;
            }

            if (!$provider instanceof ProviderInterface) {
                throw new \InvalidArgumentException(
                    sprintf("Type error token provider '%s' must implement '%s'.",
                        is_object($provider) ? get_class($provider) : gettype($provider),
                        ProviderInterface::class
                        )
                );
            }

            // Set for later reuse
            $this->providers[$idx] = $provider;
            if (null !== ($plainToken = $provider->getPlainToken())) {
                return $plainToken;
            }
        }

        return null;
    }
}
