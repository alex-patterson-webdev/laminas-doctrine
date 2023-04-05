<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Data;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @extends AbstractPluginManager<FixtureInterface>
 */
final class DataFixtureManager extends AbstractPluginManager
{
    /**
     * @var class-string<FixtureInterface>
     */
    protected $instanceOf = FixtureInterface::class;
}
