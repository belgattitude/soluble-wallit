<?php

declare(strict_types=1);

namespace Soluble\Wallit\Service;

use Soluble\Wallit\Exception;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Parser;

class JwtService implements TokenServiceInterface
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
    protected $verificationKey;

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
     * @param string $verificationKey
     * @param string $publicKey       Only needed for asymmetric support
     */
    public function __construct(Signer $signer, string $verificationKey, string $publicKey = null)
    {
        if (trim($verificationKey) === '') {
            throw new \InvalidArgumentException('Verification key (private key) cannot be empty');
        }

        if ($publicKey !== null && trim($publicKey) === '') {
            throw new \InvalidArgumentException('If public key is provided it cannot be empty');
        }

        $this->signer = $signer;
        $this->verificationKey = $verificationKey;
        $this->publicKey = $publicKey;
        $this->expiration = time() + 3600;
    }

    /**
     * Create new signed token.
     *
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

        // This will change with lcobucci v4
        $jwtBuilder->sign($this->signer, $this->verificationKey);

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
    public function parsePlainToken(string $tokenString): Token
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
    public function verifyPlainToken(string $tokenString): bool
    {
        $token = $this->parsePlainToken($tokenString);

        return $token->verify($this->signer, $this->verificationKey);
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
        return $this->verificationKey;
    }
}
