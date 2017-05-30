<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;
use ExpressiveWallitApp;

/* @var \Zend\Expressive\Application $app */

// Test for ping action
$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    return (new JsonResponse(['success' => true]))->withStatus(200);
});

$app->get('/login', [
    ExpressiveWallitApp\Action\LoginAction::class
], 'login');

$app->post('/login', [
    function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($app) {
        $method = $request->getMethod();
        if ($method !== 'POST') {
            throw new \Exception('ONLY post request is accepted');
        }

        $body = $request->getParsedBody();
        $login = $body['login'] ?? '';
        $password = $body['password'] ?? '';

        if ($login === 'demo' && $password === 'demo') {
            /**
             * @var \Soluble\Wallit\Service\JwtService
             */
            $jwtService = $app->getContainer()->get(\Soluble\Wallit\Service\JwtService::class);

            return new JsonResponse([
                'access_token' => 'test',
                'token_type'   => 'example',
            ]);
        }

        return (new JsonResponse([
            'success' => true,
            'body'    => $body
        ]))->withStatus(200);
    }
], 'login-post');
