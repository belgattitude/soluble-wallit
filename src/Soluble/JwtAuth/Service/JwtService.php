<?php

declare(strict_types=1);

namespace Soluble\JwtAuth\Service;

use Soluble\JwtAuth\Exception;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Parser;

class JwtService
{
    /**
     * @var Signer
     */
    protected $signer;

    /**
     * @var Parser
     */
    protected $tokenParser;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var string|null
     */
    protected $publicKey;

    /**
     * @var int
     */
    protected $expiration;

    /**
     * JwtService constructor.
     *
     * @param Signer $signer
     * @param string $privateKey
     * @param string $publicKey  Only needed for asymmetric
     */
    public function __construct(Signer $signer, string $privateKey, string $publicKey = null)
    {
        if (trim($privateKey) === '') {
            throw new \InvalidArgumentException('Private key key cannot be empty');
        }

        if ($publicKey !== null && trim($publicKey) === '') {
            throw new \InvalidArgumentException('If public key is provided it cannot be empty');
        }

        $this->signer = $signer;
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
        $this->expiration = time() + 3600;
    }

    /**
     * @param array $claims
     * @param int   $expiration
     *
     * @return Token
     */
    public function createToken(array $claims = [], int $expiration = null): Token
    {
        if ($expiration === null) {
            $expiration = $this->expiration;
        }

        $jwtBuilder = new Builder();

        foreach ($claims as $key => $value) {
            $jwtBuilder->set($key, $value);
        }

        $jwtBuilder->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                   ->setExpiration($expiration); // Configures the expiration time of the token (nbf claim)

        /*
        ->setIssuer('http://example.com') // Configures the issuer (iss claim)
        ->setAudience('http://example.org') // Configures the audience (aud claim)
        ->setNotBefore(time() + 60) // Configures the time that the token can be used (nbf claim)
        ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
        //$jwtBuilder->set('uid', 1); // Configures a new claim, called "uid"
        */

        $jwtBuilder->sign($this->signer, $this->privateKey);

        return $jwtBuilder->getToken();
    }

    /**
     * Parse a token string into a JWT\Token.
     *
     * @throws Exception\InvalidTokenException
     *
     * @param string $tokenString
     *
     * @return Token
     */
    public function parseTokenString(string $tokenString): Token
    {
        $tokenParser = new Parser();
        try {
            $token = $tokenParser->parse($tokenString);
        } catch (\Throwable $invalidToken) {
            throw new Exception\InvalidTokenException('Cannot parse the JWT token', 1, $invalidToken);
        }
        /*
        if (!$token->validate(new ValidationData())) {
            throw new Exception\InvalidTokenException('Validation of JWT token failed', 2);
        }
        */
        return $token;
    }

    /**
     * Parse and verify a token.
     *
     * @throws Exception\InvalidTokenException
     *
     * @param string $tokenString
     *
     * @return bool
     */
    public function verifyTokenString(string $tokenString): bool
    {
        $token = $this->parseTokenString($tokenString);

        return $token->verify($this->signer, $this->privateKey);
    }

    /**
     * @return Signer
     */
    public function getSigner(): Signer
    {
        return $this->signer;
    }

    /**
     * Return private key.
     *
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }
}
