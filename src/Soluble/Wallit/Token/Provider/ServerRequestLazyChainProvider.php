<?php

declare(strict_types=1);

namespace Soluble\Wallit\Token\Provider;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestLazyChainProvider implements ServerRequestProviderInterface
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
    public function __construct(ServerRequestInterface $request, array $providers = [])
    {
        $this->request = $request;
        $this->originalProviders = $providers;
        if (count($providers) === 0) {
            throw new \InvalidArgumentException('$providers argument is empty, at least one provider must be set');
        }
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
            } elseif (!$providerParam instanceof ProviderInterface) {
                throw new \InvalidArgumentException(
                    sprintf("Type error token provider '%s' must implement '%s'.",
                        is_object($providerParam) ? get_class($providerParam) : gettype($providerParam),
                        ProviderInterface::class
                    )
                );
            } else {
                $provider = $providerParam;
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
