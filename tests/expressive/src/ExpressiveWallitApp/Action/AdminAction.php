<?php

declare(strict_types=1);

namespace ExpressiveWallitApp\Action;

use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminAction implements RequestHandlerInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $template;

    public function __construct(
        TemplateRendererInterface $template
    ) {
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
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
