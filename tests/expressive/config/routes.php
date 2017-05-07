<?php


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/*
$app->get('/login', Soluble\Guardian\Action\LoginAction::class, 'login');
$app->post('/login', Soluble\Guardian\Action\LoginAction::class);

$app->get('/ping', [
    SolubleTest\Guardian\Examples\Action\PingAction::class
], 'ping');

$authMiddleware = Soluble\Guardian\Middleware\AuthMiddleware::class;

$app->get('/admin', [
    $authMiddleware,
    SolubleTest\Guardian\Examples\Action\AdminAction::class
], 'admin');

// Tests for OAuth middleware
$oauthMiddleware = Soluble\Guardian\Middleware\OAuthMiddleware::class;

$app->get('/oauth', [
    $oauthMiddleware,
    function (ServerRequestInterface $request, ResponseInterface $response, callable $next): JsonResponse {
        $data = [
            'action' => __METHOD__,
            'ack' => time()
        ];

        return new JsonResponse($data);
    }
], 'oauth');
*/
