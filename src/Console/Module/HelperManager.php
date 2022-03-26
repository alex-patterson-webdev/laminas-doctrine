<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Module;

use Laminas\ServiceManager\AbstractPluginManager;
use Symfony\Component\Console\Helper\HelperInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Module
 */
final class HelperManager extends AbstractPluginManager
{
    /**
     * @var string
     */
    protected $instanceOf = HelperInterface::class;
}
