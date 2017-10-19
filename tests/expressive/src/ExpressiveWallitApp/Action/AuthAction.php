<?php

declare(strict_types=1);

namespace ExpressiveWallitApp\Action;

use Fig\Http\Message\StatusCodeInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Token\Jwt\JwtClaims;
use Zend\Diactoros\Response\JsonResponse;

class AuthAction implements ServerMiddlewareInterface
{
    /**
     * @var JwtService
     */
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $method = $request->getMethod();
        if ($method !== 'POST') {
            throw new \RuntimeException('ONLY post request is accepted');
        }

        $body = $request->getParsedBody();
        $login = $body['login'] ?? '';
        $password = $body['password'] ?? '';

        if ($login === 'demo' && $password === 'demo') {
            $token = $this->jwtService->createToken([
                JwtClaims::ID => Uuid::uuid1(),
                'login'       => $login
            ]);

            return new JsonResponse([
                'access_token' => (string) $token,
                'token_type'   => 'example',
            ]);
        }

        return (new JsonResponse([
            'success' => false
        ]))->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
    }
}
