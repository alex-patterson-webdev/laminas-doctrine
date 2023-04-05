<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service;

use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\PluginManagerInterface;

/**
 * @template T
 * @extends PluginManagerInterface<T>
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
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function setService($name, $service);

    /**
     * @param string $name
     * @param string|callable $factory
     *
     * @return mixed
     *
     * @throws ContainerModificationsNotAllowedException
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function setFactory($name, $factory);
}
