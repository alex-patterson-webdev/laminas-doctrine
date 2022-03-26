<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Module\Feature;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Module\Feature
 */
interface HelperConfigProviderInterface
{
    /**
     * @return array
     */
    public function getConsoleHelperManagerConfig(): array;
}
