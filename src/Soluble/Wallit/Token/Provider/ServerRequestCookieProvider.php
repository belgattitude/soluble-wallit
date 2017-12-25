<?php

declare(strict_types=1);

namespace Soluble\Wallit\Token\Provider;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestCookieProvider implements ServerRequestProviderInterface
{
    /**
     * @var array
     */
    public const DEFAULT_OPTIONS = [
        'cookieName' => 'jwt_token'
    ];

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
     * @throws \InvalidArgumentException
     *
     * @param ServerRequestInterface $request
     * @param string[]               $options see self::DEFAULT_OPTIONS
     */
    public function __construct(ServerRequestInterface $request, array $options = [])
    {
        $this->request = $request;
        $this->cookieName = trim($options['cookieName'] ?? self::DEFAULT_OPTIONS['cookieName']);
        if ($this->cookieName === '') {
            throw new \InvalidArgumentException('cookieName option parameter cannot be empty');
        }
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
     * @return string|null
     */
    public function getPlainToken(): ?string
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
