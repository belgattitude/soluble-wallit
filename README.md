# soluble-wallit  

[![PHP Version](http://img.shields.io/badge/php-7.1+-ff69b4.svg)](https://packagist.org/packages/soluble/wallit)
[![Build Status](https://travis-ci.org/belgattitude/soluble-wallit.svg?branch=master)](https://travis-ci.org/belgattitude/soluble-wallit)
[![codecov](https://codecov.io/gh/belgattitude/soluble-wallit/branch/master/graph/badge.svg)](https://codecov.io/gh/belgattitude/soluble-wallit)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-wallit/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-wallit/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/soluble/wallit/v/stable.svg)](https://packagist.org/packages/soluble/wallit)
[![Total Downloads](https://poser.pugx.org/soluble/wallit/downloads.png)](https://packagist.org/packages/soluble/wallit)
[![License](https://poser.pugx.org/soluble/wallit/license.png)](https://packagist.org/packages/soluble/wallit)

**Experimental** work on JWT authentication with zend-expressive... Feel free to open issues or P/R :)

## Requirements

* PHP 7.1 
* [zend-expressive 2.0](https://github.com/zendframework/zend-expressive) (or any PSR-7 & PSR-15 compatible framework) 

## Install

```bash
$ composer require soluble-wallit
```
### Configure

Copy [soluble-wallit.config.php.dist](https://github.com/belgattitude/soluble-wallit/blob/master/config/soluble-wallit.config.php.dist) in your autoload directory.

```bash
cp ./vendor/soluble/wallit/config/soluble-wallit.config.php.dist ./config/autoload/soluble-wallit.config.local.php
```

Edit the config file and add your token keys 

### Register

Ensure `Soluble\Wallit\Config\ConfigProvider::class` is registered in the `./config/config.php` file. 

```php
<?php
use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
    'config_cache_path' => 'data/config-cache.php',
];

$aggregator = new ConfigAggregator([
    new ArrayProvider($cacheConfig),
    
    // Register the Soluble Wallit ConfigProvider    
    Soluble\Wallit\Config\ConfigProvider::class,
    
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
    new PhpFileProvider('config/development.config.php'),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
``` 

## Documentation

Latest examples and configuration can be found in the [expressive directory](https://github.com/belgattitude/soluble-wallit/tree/master/tests/expressive) used
by smoke tests. Otherwise look at the examples below:


### Authentication

Create an action that will authenticate and generate a token. Here's the factory:

```php
<?php declare(strict_types=1);

namespace ExpressiveWallitApp\Action;

class AuthActionFactory
{
    public function __invoke(ContainerInterface $container): AuthAction
    {
        return new AuthAction($container->get(JwtService::class));
    }
}
```
And the action:

```php
<?php declare(strict_types=1);

namespace ExpressiveWallitApp\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Token\Jwt\JwtClaims;
use Zend\Diactoros\Response\JsonResponse;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface;
use Webimpress\HttpMiddlewareCompatibility\MiddlewareInterface as ServerMiddlewareInterface;

class AuthAction implements ServerMiddlewareInterface
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function process(ServerRequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        $body = $request->getParsedBody();
        $login = $body['login'] ?? '';
        $password = $body['password'] ?? '';

        if ($login === 'demo' && $password === 'demo') {
            $token = $this->jwtService->createToken([
                JwtClaims::ID => Uuid::uuid1(),
                'login'       => $login
            ]);
            return new JsonResponse([
                'access_token' => (string) $token,
                'token_type'   => 'example',
            ]);
        }
        return (new JsonResponse([
            'success' => false
        ]))->withStatus(401); // Unauthorized
    }
}
```

Set the route in `./config/routes.php` 

```php
<?php
//....
$app->post('/auth', [
    \ExpressiveWallitApp\Action\AuthAction::class
], 'auth');
```
  
### Protect an action

Simply pipe or add the `JwtAuthMiddleware::class` to the desired route. As an example in the `./config/routes.php` file :

```php
<?php

// ...
$app->get('/admin', [
    \Soluble\Wallit\Middleware\JwtAuthMiddleware::class,
    \ExpressiveWallitApp\Action\AdminAction::class
], 'admin');

```

### How to retrieve the token

In case you need it, the token is available as a request attribute: `$request->getAttribute(JwtAuthMiddleware::class)`.

```php
<?php declare(strict_types=1);

namespace ExpressiveWallitApp\Action;

use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface;
use Webimpress\HttpMiddlewareCompatibility\MiddlewareInterface as ServerMiddlewareInterface;

class AdminAction implements ServerMiddlewareInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $template;

    public function __construct(TemplateRendererInterface $template) {
        $this->template = $template;
    }

    public function process(ServerRequestInterface $request, HandlerInterface $delegate): ResponseInterface
    {
        $token = $this->getTokenFromRequest($request);

        return new HtmlResponse($this->template->render('pages::admin', [
            'login' => $token->getClaim('login')
        ]));
    }
   
    protected function getTokenFromRequest(ServerRequestInterface $request): Token
    {
        return $request->getAttribute(JwtAuthMiddleware::class);
    }
}
```
  
## Coding standards and interop

* [fig/http-message-util](https://github.com/php-fig/http-message-util) Utility classes and constants for use with PSR-7 (psr/http-message)
* [psr/http-message](http://www.php-fig.org/psr/psr-7/) Common interface for HTTP messages (PHP FIG PSR-7)
* [psr/container](http://www.php-fig.org/psr/psr-11/) Common Container Interface (PHP FIG PSR-11)

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)

