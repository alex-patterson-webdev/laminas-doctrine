<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Persistence;

use Laminas\ServiceManager\AbstractPluginManager;

class PersistServiceManager extends AbstractPluginManager
{
    protected $instanceOf = PersistServiceInterface::class;
}
