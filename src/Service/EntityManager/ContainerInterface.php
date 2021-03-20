<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\EntityManager;

use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\PluginManagerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service\EntityManager
 */
interface ContainerInterface extends PluginManagerInterface
{
    /**
     * @param string $name
     * @param mixed  $service
     *
     * @return mixed
     *
     * @throws ContainerModificationsNotAllowedException
     * @noinspection PhpMissingParamTypeInspection
     */
    public function setService($name, $service);

    /**
     * Specify a factory for a given service name.
     *
     * @param string          $name
     * @param string|callable $factory
     *
     * @return mixed
     *
     * @throws ContainerModificationsNotAllowedException
     * @noinspection PhpMissingParamTypeInspection
     */
    public function setFactory($name, $factory);
}
