<?php


return [
    // Enable debugging; typically used to provide debugging information within templates.
    'debug' => true,
    'zend-expressive' => [
        // Enable exception-based error handling via standard middleware.
        'raise_throwables' => true,
        // Enable programmatic pipeline: Any `middleware_pipeline` or `routes`
        // configuration will be ignored when creating the `Application` instance.
        'programmatic_pipeline' => true
    ],
];
