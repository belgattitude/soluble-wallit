<?php

declare(strict_types=1);

namespace SolubleTest\Wallit\Service;

use PHPUnit\Framework\TestCase;
use Soluble\Wallit\Token\Exception as TokenException;
use Soluble\Wallit\Service\JwtService;
use Lcobucci\JWT\Signer;

class JwtServiceTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testConstructorThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $signer = new Signer\Hmac\Sha256();
        new JwtService($signer, $verificationKey = '');
    }

    public function testConstructorThrowsInvalidArgumentExceptionAsym(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $signer = new Signer\Hmac\Sha256();
        new JwtService($signer, 'secret', $public_key = '');
    }

    public function testCreateTokenWithSymmetricHmac(): void
    {
        $signer = new Signer\Hmac\Sha256();
        $privateKey = 'the-secret-symmetric-key-for-symmetric-hmac-algo';
        $service = new JwtService($signer, $privateKey);
        $token = $service->createToken($claims = [
            'uid' => 1999, // Custom claim
            'aud' => 'https://example.org' // Audience claim
        ]);

        self::assertTrue($token->verify($signer, $privateKey));
        self::assertFalse($token->verify($signer, 'invalid_key'));

        self::assertFalse($token->isExpired());

        self::assertEquals('https://example.org', $token->getClaim('aud'));
        self::assertEquals(1999, $token->getClaim('uid'));
    }

    public function testParseTokenStringHmac(): void
    {
        $hs256Signer = new Signer\Hmac\Sha256();
        $jwtService = $this->getSymmetricJwtService('private-key', $hs256Signer);

        // example token from jwt.io (privateKey: secret)
        $jwt_io = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';

        $token1 = $jwtService->parsePlainToken($jwt_io);

        self::assertTrue($token1->verify($hs256Signer, 'secret'));
        self::assertFalse($token1->verify($hs256Signer, 'private-key'));

        self::assertEquals('John Doe', $token1->getClaim('name'));

        // test token self-signed with private key: the-secret-symmetric-key-for-symmetric-hmac-algo
        $token256 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE0OTQxNzYxMDMsImV4cCI6MTQ5NDE3OTcwMywidWlkIjoxOTk5LCJhdWQiOiJodHRwczpcL1wvZXhhbXBsZS5vcmcifQ.cJ_rAzbQjuM0HKAYHabR1BgZmNZZgV4FjWQkuLgnrnk';

        $token2 = $jwtService->parsePlainToken($token256);

        self::assertTrue($token2->verify($hs256Signer, 'the-secret-symmetric-key-for-symmetric-hmac-algo'));
        self::assertFalse($token2->verify($hs256Signer, 'private-key'));
    }

    public function testParseTokenDifferentAlgos(): void
    {
        $jwtService = $this->getSymmetricJwtService('private-key', new Signer\Hmac\Sha384());

        $token384 = $jwtService->createToken();

        self::assertFalse($token384->verify(new Signer\Hmac\Sha256(), 'private-key'));
        self::assertTrue($token384->verify(new Signer\Hmac\Sha384(), 'private-key'));
    }

    public function testParseTokenStringThrowsInvalidTokenException(): void
    {
        $this->expectException(TokenException\InvalidTokenException::class);
        $jwtService = $this->getSymmetricJwtService('private-key');

        $tokenString = 'eyJhbGciOiJIUzI1NiIsInR5cCI';
        $jwtService->parsePlainToken($tokenString);
    }

    public function testVerifyTokenString(): void
    {
        $jwtService = $this->getSymmetricJwtService('private-key', new Signer\Hmac\Sha384());

        $token384 = $jwtService->createToken();

        self::assertTrue($jwtService->verifyPlainToken($token384->__toString()));
    }

    public function testVerifyTokenStringThrowsInvalidTokenException(): void
    {
        $this->expectException(TokenException\InvalidTokenException::class);
        $jwtService = $this->getSymmetricJwtService('private-key');

        $tokenString = 'eyJhbGciOiJIUzI1NiIsInR5cCI';
        $jwtService->verifyPlainToken($tokenString);
    }

    protected function getSymmetricJwtService(string $privateKey, Signer $signer = null): JwtService
    {
        if ($signer === null) {
            $signer = new Signer\Hmac\Sha256();
        }

        return new JwtService($signer, $privateKey);
    }
}
