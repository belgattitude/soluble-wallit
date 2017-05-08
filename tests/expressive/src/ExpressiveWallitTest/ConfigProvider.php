<?php

declare(strict_types=1);

namespace ExpressiveWallitTest;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies()
        ];
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                // Actions
                Action\LoginAction::class => Action\LoginActionFactory::class,
            ],
        ];
    }
}
