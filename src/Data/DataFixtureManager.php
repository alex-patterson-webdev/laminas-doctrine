<?php


namespace Arp\LaminasDoctrine\Data;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Data
 */
final class DataFixtureManager extends AbstractPluginManager
{
    /**
     * @var string
     */
    protected $instanceOf = FixtureInterface::class;
}
