# soluble-wallit  

[![PHP Version](http://img.shields.io/badge/php-7.1+-ff69b4.svg)](https://packagist.org/packages/soluble/wallit)
[![Build Status](https://travis-ci.org/belgattitude/soluble-wallit.svg?branch=master)](https://travis-ci.org/belgattitude/soluble-wallit)
[![codecov](https://codecov.io/gh/belgattitude/soluble-wallit/branch/master/graph/badge.svg)](https://codecov.io/gh/belgattitude/soluble-wallit)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-wallit/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-wallit/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/soluble/wallit/v/stable.svg)](https://packagist.org/packages/soluble/wallit)
[![Total Downloads](https://poser.pugx.org/soluble/wallit/downloads.png)](https://packagist.org/packages/soluble/wallit)
[![License](https://poser.pugx.org/soluble/wallit/license.png)](https://packagist.org/packages/soluble/wallit)

**Experimental** PSR-15 Middleware for dealing with JWT generation and checks.  

## Requirements

* PHP 7.1  

## Recommended 

* [zend-expressive 3.0](https://github.com/zendframework/zend-expressive) (or any PSR-7 & PSR-15 compatible framework)

> For zend-expressive 2.0 use the 0.3 release.

## Install

```bash
$ composer require soluble-wallit
```

## Configure

Copy [soluble-wallit.config.php.dist](https://github.com/belgattitude/soluble-wallit/blob/master/config/soluble-wallit.config.php.dist) in your autoload directory.

```bash
cp ./vendor/soluble/wallit/config/soluble-wallit.config.php.dist ./config/autoload/soluble-wallit.config.local.php
```

Edit the config file and add your token keys 

## Register (zend-expressive 3.0)

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

## Usage (zend-expressive 3.0)

To quickly browse the examples, see the [smoke tests directory](https://github.com/belgattitude/soluble-wallit/tree/master/tests/server/expressive/src/App/Handler). 

### Example 1

Create a PSR-15 handler to generate a JWT token upon successful authentication:

```php
<?php declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Fig\Http\Message\StatusCodeInterface;
use Ramsey\Uuid\Uuid;
use Soluble\Wallit\Service\JwtService;
use Soluble\Wallit\Token\Jwt\JwtClaims;
use Zend\Diactoros\Response\JsonResponse;

class AuthHandler implements RequestHandlerInterface
{
    /**
     * @var JwtService
     */
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        if ($method !== 'POST') {
            throw new \RuntimeException('TODO - Handle error your way ;)');
        }

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
        ]))->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
    }
}

```

Its related factory can be:

```php
<?php declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Soluble\Wallit\Service\JwtService;

class AuthHandlerFactory
{
    public function __invoke(ContainerInterface $container): AuthHandler
    {
        return new AuthHandler(
            $container->get(JwtService::class)
        );
    }
}
```

Add a route in `./config/routes.php` 

```php
<?php
//....
$app->post('/auth', App\Handler\AuthHandler::class, 'auth');
```
  
### Example 2: Check JWT

Simply pipe or add the `JwtAuthMiddleware::class` to the desired route. 

As an example in the `./config/routes.php` file :

```php
<?php declare(strict_types=1);

use Soluble\Wallit\Middleware\JwtAuthMiddleware;

// ...
    $app->get('/admin', [
        JwtAuthMiddleware::class,
        App\Handler\AdminHandler::class
    ], 'admin');

```

### Example 3: Retrive the token

The token is available as a request attribute: `$request->getAttribute(JwtAuthMiddleware::class)`.

```php
<?php declare(strict_types=1);
namespace App\Handler;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Soluble\Wallit\Middleware\JwtAuthMiddleware;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Lcobucci\JWT\Token;

class AdminHandler implements RequestHandlerInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $template;
    public function __construct(TemplateRendererInterface $template) {
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
    protected function getTokenFromRequest(ServerRequestInterface $request): Token
    {
        return $request->getAttribute(JwtAuthMiddleware::class);
    }
}
```
  
## Standards

* [fig/http-message-util](https://github.com/php-fig/http-message-util) Utility classes and constants for use with PSR-7 (psr/http-message)
* [psr/http-message](http://www.php-fig.org/psr/psr-7/) Common interface for HTTP messages (PHP FIG PSR-7)
* [psr/container](http://www.php-fig.org/psr/psr-11/) Common Container Interface (PHP FIG PSR-11)
* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)

