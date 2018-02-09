<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    // Test for ping action
    $app->get('/', new class() implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return (new JsonResponse(['success' => true]))->withStatus(200);
        }
    });

    $app->get('/login', App\Handler\LoginHandler::class, 'login');

    $app->post('/auth', App\Handler\AuthHandler::class, 'auth');

    $app->get('/admin', [
        Soluble\Wallit\Middleware\JwtAuthMiddleware::class,
        App\Handler\AdminHandler::class
    ], 'admin');
};
