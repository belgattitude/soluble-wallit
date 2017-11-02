<?php

declare(strict_types=1);

namespace ExpressiveWallitApp\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class AdminAction implements ServerMiddlewareInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $template;

    /**
     * LoginAction constructor.
     *
     * @param TemplateRendererInterface $template
     */
    public function __construct(
        TemplateRendererInterface $template
    ) {
        $this->template = $template;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $token = $this->getTokenFromRequest($request);

        return new HtmlResponse($this->template->render('pages::admin', [
            'token' => $token,
            'login' => $token->getClaim('login')
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     */
    protected function getTokenFromRequest(ServerRequestInterface $request): Token
    {
        return $request->getAttribute(JwtAuthMiddleware::class);
    }
}
