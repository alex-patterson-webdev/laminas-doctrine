<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Service\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
final class EntityManagerContainer extends AbstractPluginManager implements ContainerInterface
{
    /**
     * @var class-string<EntityManagerInterface>
     */
    protected $instanceOf = EntityManagerInterface::class;
}
