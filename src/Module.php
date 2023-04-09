<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine;

use Arp\LaminasDoctrine\Console\Module\CommandManager;
use Arp\LaminasDoctrine\Console\Module\Feature\CommandConfigProviderInterface;
use Arp\LaminasDoctrine\Console\Module\Feature\HelperConfigProviderInterface;
use Arp\LaminasDoctrine\Console\Module\HelperManager;
use Laminas\ModuleManager\Listener\ServiceListenerInterface;
use Laminas\ModuleManager\ModuleManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class Module
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function init(ModuleManager $moduleManager): void
    {
        /** @var ContainerInterface $serviceManager */
        $serviceManager = $moduleManager->getEvent()->getParam('ServiceManager');

        /** @var ServiceListenerInterface $serviceListener */
        $serviceListener = $serviceManager->get('ServiceListener');

        $serviceListener->addServiceManager(
            CommandManager::class,
            'arp_console_command_manager',
            CommandConfigProviderInterface::class,
            'getConsoleCommandManagerConfig'
        );

        $serviceListener->addServiceManager(
            HelperManager::class,
            'arp_console_helper_manager',
            HelperConfigProviderInterface::class,
            'getConsoleHelperManagerConfig'
        );
    }

    /**
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        return array_replace_recursive(
            require __DIR__ . '/../config/doctrine.config.php',
            require __DIR__ . '/../config/console.config.php',
            require __DIR__ . '/../config/module.config.php'
        );
    }
}
