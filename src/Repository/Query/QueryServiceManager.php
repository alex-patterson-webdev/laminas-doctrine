<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Query;

use Laminas\ServiceManager\AbstractPluginManager;

class QueryServiceManager extends AbstractPluginManager
{
    protected $instanceOf = QueryServiceInterface::class;
}
