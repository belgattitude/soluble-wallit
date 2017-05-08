<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;
use ExpressiveWallitTest;

/* @var \Zend\Expressive\Application $app */

// Test for ping action
$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    return (new JsonResponse(['success' => true]))->withStatus(200);
});

$app->get('/login', [
    ExpressiveWallitTest\Action\LoginAction::class
], 'login');

$app->post('/login', function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    return (new JsonResponse(['success' => true]))->withStatus(200);
});

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
