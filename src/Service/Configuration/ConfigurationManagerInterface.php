<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Configuration;

use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationManagerException;
use Doctrine\ORM\Configuration;

interface ConfigurationManagerInterface
{
    public function hasConfiguration(string $name): bool;

    /**
     * @param array<string, mixed> $config
     */
    public function addConfigurationConfig(string $name, array $config): void;

    /**
     * @throws ConfigurationManagerException
     */
    public function getConfiguration(string $name): Configuration;

    /**
     * @param iterable<string, Configuration> $configurations
     */
    public function setConfigurations(iterable $configurations): void;

    public function setConfiguration(string $name, Configuration $configuration): void;
}
