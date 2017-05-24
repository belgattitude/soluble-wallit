<?php

declare(strict_types=1);

namespace Soluble\Wallit\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\Wallit\Token\Provider\ServerRequestCookieProvider;
use Soluble\Wallit\Token\Provider\ServerRequestAuthBearerProvider;
use Soluble\Wallit\Token\Provider\ServerRequestLazyChainProvider;
use Soluble\Wallit\Service\JwtService;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;

class JwtAuthMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var array
     */
    protected $options = [
      'secure'  => true,
      'relaxed' => [
      ]
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

        // 1. Check for secure scheme

        // 2. Fetch token from server request

        $tokenProvider = new ServerRequestLazyChainProvider($request, [
            [ServerRequestAuthBearerProvider::class => [
                'httpHeader'       => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeader'],
                'httpHeaderPrefix' => ServerRequestAuthBearerProvider::DEFAULT_OPTIONS['httpHeaderPrefix'],
            ]],
            [ServerRequestCookieProvider::class => [
                'cookieName' => ServerRequestCookieProvider::DEFAULT_OPTIONS['cookieName']
            ]]
        ]);

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
