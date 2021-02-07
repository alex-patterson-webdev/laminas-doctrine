<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine;

use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine
 */
final class Module implements DependencyIndicatorInterface
{
    /**
     * @return array
     */
    public function getModuleDependencies(): array
    {
        return [
            'Arp\\LaminasDateTime',
            'Arp\\LaminasDoctrine\\Query',
            'Arp\\LaminasSymfonyConsole',
        ];
    }

    /**
     * Return the module configuration array.
     *
     * @return array
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
