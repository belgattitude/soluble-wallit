<?php

declare(strict_types=1);

namespace Soluble\JwtAuth\Jwt\Provider;

use Psr\Http\Message\ServerRequestInterface;

class RequestAuthBearerProvider implements JwtProviderInterface
{
    protected const HEADER = 'Authentication';
    protected const HEADER_PREFIX = 'Bearer ';

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
     * HttpAuthenticationBearer constructor.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
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
    public function getTokenString(): ?string
    {
        if (!$this->loaded) {
            $this->loadTokenFromRequest();
        }

        return $this->tokenString;
    }

    protected function loadTokenFromRequest(): void
    {
        $headers = $this->request->getHeader(self::HEADER);
        foreach ($headers as $header) {
            if (strpos($header, self::HEADER_PREFIX) === 0) {
                $this->tokenString = str_replace(self::HEADER_PREFIX, '', $header);
            }
        }
        $this->loaded = true;
    }
}
