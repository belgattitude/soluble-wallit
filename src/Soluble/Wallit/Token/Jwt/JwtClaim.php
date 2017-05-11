<?php

declare(strict_types=1);

namespace Soluble\Wallit\Token;

interface JwtClaim
{
    const ALL = [
        self::AUDIENCE,
        self::ID,
        self::EXPIRATION_TIME,
        self::NOT_BEFORE,
        self::ISSUED_AT,
        self::ISSUER,
        self::SUBJECT
    ];

    const AUDIENCE = 'aud';
    const ID = 'jti';
    const ISSUER = 'iss';
    const SUBJECT = 'sub';

    const ISSUED_AT = 'iat';
    const EXPIRATION_TIME = 'exp';
    const NOT_BEFORE = 'nbf';
}
