<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Persistence;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Repository\Persistence
 */
class PersistServiceManager extends AbstractPluginManager
{
    protected $instanceOf = PersistServiceInterface::class;
}
