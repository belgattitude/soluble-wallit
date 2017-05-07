<?php

declare(strict_types=1);

namespace Soluble\Wallit\Jwt\Provider;

use Psr\Http\Message\ServerRequestInterface;

class RequestCookieProvider implements JwtProviderInterface
{
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
    private $cookieName;

    /**
     * HttpAuthenticationBearer constructor.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request,
                                string $cookieName
                                ) {
        $this->request = $request;
        $this->cookieName = $cookieName;
    }

    /**
     * @return bool
     */
    public function hasToken(): bool
    {
        if (!$this->loaded) {
            $this->loadTokenFromCookie();
        }

        return $this->tokenString !== null;
    }

    /**
     * Return token string.
     *
     *
     * @return string
     */
    public function getTokenString(): ?string
    {
        if (!$this->loaded) {
            $this->loadTokenFromCookie();
        }

        return $this->tokenString;
    }

    protected function loadTokenFromCookie(): void
    {
        $cookies = $this->request->getCookieParams();
        $this->tokenString = $cookies[$this->cookieName] ?? null;
        $this->loaded = true;
    }
}
