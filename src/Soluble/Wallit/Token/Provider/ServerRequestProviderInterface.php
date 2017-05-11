<?php

declare(strict_types=1);

namespace Soluble\Wallit\Token\Provider;

use Psr\Http\Message\ServerRequestInterface;

interface ServerRequestProviderInterface extends ProviderInterface
{
    /**
     * ServerRequestProviderInterface constructor.
     *
     * @param ServerRequestInterface $request
     * @param array                  $options
     */
    public function __construct(ServerRequestInterface $request, array $options = []);
}
