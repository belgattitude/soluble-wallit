<?php

declare(strict_types=1);

namespace Soluble\Wallit\Jwt\Provider;

interface JwtProviderInterface
{
    /**
     * Checks whether a token have been provided.
     *
     * @return bool
     */
    public function hasToken(): bool;

    /**
     * Return provided token as it has been provided.
     *
     * @return string|null
     */
    public function getTokenString(): ?string;
}
