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
    \ExpressiveWallitApp\Action\LoginAction::class
], 'login');


$app->post('/auth', [
    \ExpressiveWallitApp\Action\AuthAction::class
], 'auth');
