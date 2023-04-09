<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Service\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @extends AbstractPluginManager<EntityManagerInterface>
 * @implements ContainerInterface<EntityManagerInterface>
 */
final class EntityManagerContainer extends AbstractPluginManager implements ContainerInterface
{
    /**
     * @var class-string<EntityManagerInterface>
     */
    protected $instanceOf = EntityManagerInterface::class;
}
