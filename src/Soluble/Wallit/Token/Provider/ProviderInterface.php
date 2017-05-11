<?php

declare(strict_types=1);

namespace Soluble\Wallit\Token\Provider;

interface ProviderInterface
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
    public function getPlainToken(): ?string;
}
