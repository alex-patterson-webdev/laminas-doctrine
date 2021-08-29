<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Configuration;

use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationManagerException;
use Doctrine\ORM\Configuration;

/**
 * @deprecated
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service\Configuration
 */
interface ConfigurationManagerInterface
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConfiguration(string $name): bool;

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function addConfigurationConfig(string $name, array $config): void;

    /**
     * @param string $name
     *
     * @return Configuration
     *
     * @throws ConfigurationManagerException
     */
    public function getConfiguration(string $name): Configuration;

    /**
     * @param iterable<string, Configuration> $configurations
     */
    public function setConfigurations(iterable $configurations): void;

    /**
     * @param string        $name
     * @param Configuration $configuration
     */
    public function setConfiguration(string $name, Configuration $configuration): void;
}
