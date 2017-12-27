<?php

declare(strict_types=1);

namespace Soluble\Wallit\Middleware;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Token\Provider\ServerRequestLazyChainProvider;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;

class JwtAuthMiddleware implements MiddlewareInterface
{
    public const DEFAULT_OPTIONS = [
        self::OPTION_ALLOW_INSECURE_HTTP => false,
        self::OPTION_RELAXED_HOSTS       => [],
    ];

    public const OPTION_ALLOW_INSECURE_HTTP = 'allow_insecure_http';
    public const OPTION_RELAXED_HOSTS = 'relaxed_hosts';

    /**
     * @var array
     */
    protected $tokenProviders = [];

    /**
     * @var JwtService
     */
    protected $jwtService;

    /**
     * @var array
     */
    protected $options;

    /**
     * JwtAuthMiddleware constructor.
     *
     * @param mixed[] $tokenProviders lazy loaded token providers
     * @param mixed[] $options
     */
    public function __construct(
                    array $tokenProviders,
                    JwtService $jwtService,
                    array $options = []
    ) {
        $this->tokenProviders = $tokenProviders;
        $this->jwtService = $jwtService;
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);
    }

    /**
     * @throws Exception\InsecureSchemeException
     *
     * @return ResponseInterface|RedirectResponse
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 1. Check for secure scheme (with exception of relaxed_hosts)
        $scheme = strtolower($request->getUri()->getScheme());

        if ($this->options['allow_insecure_http'] !== true && $scheme !== 'https') {
            $host = $request->getUri()->getHost();
            $relaxed_hosts = (array) $this->options['relaxed_hosts'];
            if (!in_array($host, $relaxed_hosts, true)) {
                throw new Exception\InsecureSchemeException(sprintf(
                    'Insecure scheme (%s) denied by configuration.',
                    $scheme
                ));
            }
        }

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
                        // Authenticated
                        return $handler->handle($request->withAttribute(self::class, $token));
                    }
                } else {
                    $message = 'Token is invalid';
                }
            } catch (\Throwable $e) {
                // log something ?
                $message = 'Token error while parsing plain text';
            }
        } else {
            $message = 'No token provided';
        }

        $error = new JsonResponse([
            'message' => 'Unauthorized.',
            'reason'  => $message,
            'code'    => 401
        ], 401, []);

        return $error;
    }
}
