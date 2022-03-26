<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Module;

use Laminas\ServiceManager\AbstractPluginManager;
use Symfony\Component\Console\Command\Command;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Console\Module
 */
class CommandManager extends AbstractPluginManager
{
    /**
     * @var class-string<Command>
     */
    protected $instanceOf = Command::class;
}
