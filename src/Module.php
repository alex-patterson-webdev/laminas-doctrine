<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine
 */
final class Module
{
    /**
     * Return the module configuration array.
     *
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
