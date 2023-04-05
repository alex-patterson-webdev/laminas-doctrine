<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Query;

use Arp\Entity\EntityInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @extends AbstractPluginManager<QueryServiceInterface>
 */
final class QueryServiceManager extends AbstractPluginManager
{
    /**
     * @var class-string<QueryServiceInterface<EntityInterface>>
     */
    protected $instanceOf = QueryServiceInterface::class;
}
