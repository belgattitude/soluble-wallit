<?php

declare(strict_types=1);

namespace Soluble\Wallit\Token\Provider;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestAuthBearerProvider implements ServerRequestProviderInterface
{
    /**
     * @var array
     */
    public const DEFAULT_OPTIONS = [
        self::OPTION_HTTP_HEADER        => 'Authentication',
        self::OPTION_HTTP_HEADER_PREFIX => 'Bearer '
    ];

    /**
     * @var string
     */
    public const OPTION_HTTP_HEADER = 'httpHeader';

    /**
     * @var string
     */
    public const OPTION_HTTP_HEADER_PREFIX = 'httpHeaderPrefix';

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var string|null
     */
    private $tokenString;

    /**
     * @var string
     */
    private $httpHeader;

    /**
     * @var string
     */
    private $httpHeaderPrefix;

    /**
     * HttpAuthenticationBearer constructor.
     *
     * @throws \InvalidArgumentException
     *
     * @param ServerRequestInterface $request
     * @param array                  $options
     */
    public function __construct(ServerRequestInterface $request, array $options = [])
    {
        $this->request = $request;

        $this->httpHeader = trim((string) ($options['httpHeader'] ?? self::DEFAULT_OPTIONS['httpHeader']));
        $this->httpHeaderPrefix = (string) ($options['httpHeaderPrefix'] ?? self::DEFAULT_OPTIONS['httpHeaderPrefix']);

        if ($this->httpHeader === '') {
            throw new \InvalidArgumentException('httpHeader option cannot be empty.');
        }
    }

    /**
     * @return bool
     */
    public function hasToken(): bool
    {
        if (!$this->loaded) {
            $this->loadTokenFromRequest();
        }

        return $this->tokenString !== null;
    }

    /**
     * Return token string.
     *
     * @return string|null
     */
    public function getPlainToken(): ?string
    {
        if (!$this->loaded) {
            $this->loadTokenFromRequest();
        }

        return $this->tokenString;
    }

    protected function loadTokenFromRequest(): void
    {
        $headers = $this->request->getHeader($this->httpHeader);
        foreach ($headers as $header) {
            if ($this->httpHeaderPrefix !== '') {
                if (strpos($header, $this->httpHeaderPrefix) === 0) {
                    $this->tokenString = trim(str_replace($this->httpHeaderPrefix, '', $header));
                }
            } else {
                $this->tokenString = trim($header);
            }
        }
        $this->loaded = true;
    }
}
