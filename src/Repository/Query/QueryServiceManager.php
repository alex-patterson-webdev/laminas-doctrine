<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Query;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Repository\Query
 */
class QueryServiceManager extends AbstractPluginManager
{
    protected $instanceOf = QueryServiceInterface::class;
}
