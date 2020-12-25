<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
final class EntityManagerManager extends AbstractPluginManager
{
    /**
     * @var string
     */
    protected $instanceOf = EntityManagerInterface::class;
}
