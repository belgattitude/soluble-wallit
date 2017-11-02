<?php

declare(strict_types=1);

namespace ExpressiveWallitApp;

class ConfigProvider
{
    /**
     * @return mixed[]
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies()
        ];
    }

    /**
     * @return mixed[]
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                // Actions
                Action\LoginAction::class  => Action\LoginActionFactory::class,
                Action\AuthAction::class   => Action\AuthActionFactory::class,
                Action\AdminAction::class  => Action\AdminActionFactory::class
            ],
        ];
    }
}
