<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Module;

use Laminas\ServiceManager\AbstractPluginManager;
use Symfony\Component\Console\Helper\HelperInterface;

final class HelperManager extends AbstractPluginManager
{
    /**
     * @var class-string<HelperInterface>
     */
    protected $instanceOf = HelperInterface::class;
}
