<?php

declare(strict_types=1);

namespace Soluble\JwtAuth;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\JwtAuth\Jwt\Provider\RequestCookieProvider;
use Soluble\JwtAuth\Jwt\Provider\RequestAuthBearerProvider;
use Soluble\JwtAuth\Service\JwtService;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;

class JwtAuthMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var array
     */
    protected $options = [
      'secure' => true,
      'relaxed' => []
    ];

    /**
     * @var JwtService
     */
    protected $jwtService;

    /**
     * JwtAuthMiddleware constructor.
     *
     * @param JwtService $jwtService
     */
    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface|RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $authenticated = false;

        $tokenString = $this->getTokenString($request);

        if ($tokenString !== null) {
            try {
                $token = $this->jwtService->parseTokenString($tokenString);

                if ($token->verify($this->jwtService->getSigner(), $this->jwtService->getPrivateKey())) {
                    if ($token->isExpired()) {
                        $message = 'Token has expired';
                    } else {
                        $authenticated = true;
                        // log Something ?
                        $response = $delegate->process($request->withAttribute(self::class, $token));
                        // do something with the response (writing cookie, refresh token ?)
                        return $response;
                    }
                } else {
                    $message = 'Token is invalid';
                }
            } catch (\Throwable $e) {
                // log something ?
                $message = 'Token error';
            }
        } else {
            $message = 'No token provided';
        }

        // @todo: ask the correct way with PSR-15 ?
        $error = new JsonResponse([
            'message' => 'Unauthorized.',
            'reason' => $message,
            'code' => 401
        ], 401, []);

        return $error;
    }

    /**
     * Return token string.
     *
     * Will be read from HTTP "Authentication: Bearer" header
     * then from cookie
     *
     * @param ServerRequestInterface $request
     *
     * @return null|string
     */
    protected function getTokenString(ServerRequestInterface $request): ?string
    {
        if (null === ($tokenString = (new RequestAuthBearerProvider($request))->getTokenString())) {
            $tokenString = (new RequestCookieProvider($request, 'jwtcookie'))->getTokenString();
        }

        return $tokenString;
    }
}
