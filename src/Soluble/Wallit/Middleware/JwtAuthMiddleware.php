<?php

declare(strict_types=1);

namespace Soluble\Wallit\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Token\Provider\ServerRequestLazyChainProvider;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;

class JwtAuthMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var array
     */
    protected $tokenProviders = [];

    /**
     * @var JwtService
     */
    protected $jwtService;

    /**
     * JwtAuthMiddleware constructor.
     *
     * @param array      $tokenProviders lazy loaded token providers
     * @param JwtService $jwtService
     */
    public function __construct(array $tokenProviders,
                                JwtService $jwtService)
    {
        $this->tokenProviders = $tokenProviders;
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
        // 1. Check for secure scheme

        // 2. Fetch token from server request

        $tokenProvider = new ServerRequestLazyChainProvider($request, $this->tokenProviders);

        $plainToken = $tokenProvider->getPlainToken();

        // 3. Validate the token
        if ($plainToken !== null) {
            try {
                $token = $this->jwtService->parsePlainToken($plainToken);

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
            'reason'  => $message,
            'code'    => 401
        ], 401, []);

        return $error;
    }
}
