<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Persistence;

use Arp\Entity\EntityInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @extends AbstractPluginManager<PersistServiceInterface>
 */
class PersistServiceManager extends AbstractPluginManager
{
    /**
     * @var class-string<PersistServiceInterface<EntityInterface>>
     */
    protected $instanceOf = PersistServiceInterface::class;
}
