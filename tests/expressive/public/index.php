<?php

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

$base_dir = dirname(dirname(dirname(__DIR__)));

require $base_dir . '/vendor/autoload.php';
chdir(dirname(__DIR__));

(function ($config_dir) {
    /** @var \Interop\Container\ContainerInterface $container */
    $container = require $config_dir . '/config/container.php';

    /** @var \Zend\Expressive\Application $app */
    $app = $container->get(\Zend\Expressive\Application::class);
    require $config_dir . '/config/pipeline.php';
    require $config_dir . '/config/routes.php';
    $app->run();
})(dirname(__DIR__));
