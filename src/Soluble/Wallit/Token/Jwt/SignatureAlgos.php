<?php

declare(strict_types=1);

namespace Soluble\Wallit\Token\Jwt;

interface SignatureAlgos
{
    /**
     * All supported algos.
     *
     * @var array
     */
    const ALL = [
        self::HS256,
        self::HS384,
        self::HS512,
    ];

    /**
     * Symmetric type algos.
     */
    const SYMMETRIC = [
        self::HS256,
        self::HS384,
        self::HS512,
    ];

    const HS256 = 'HS256';
    const HS384 = 'HS384';
    const HS512 = 'HS512';
}
