<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Module\Feature;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Console\Module\Feature
 */
interface CommandConfigProviderInterface
{
    /**
     * @return array
     */
    public function getConsoleCommandManagerConfig(): array;
}
