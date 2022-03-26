<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Configuration;

use Arp\LaminasDoctrine\Config\ConfigurationConfigs;
use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationFactoryException;
use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationManagerException;
use Doctrine\ORM\Configuration;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
final class ConfigurationManager implements ConfigurationManagerInterface
{
    /**
     * @var ConfigurationFactoryInterface
     */
    private ConfigurationFactoryInterface $configurationFactory;

    /**
     * @var ConfigurationConfigs
     */
    private ConfigurationConfigs $configs;

    /**
     * @var Configuration[]
     */
    private array $configurations = [];

    /**
     * @param ConfigurationFactoryInterface $configurationFactory
     * @param ConfigurationConfigs          $configs
     */
    public function __construct(ConfigurationFactoryInterface $configurationFactory, ConfigurationConfigs $configs)
    {
        $this->configurationFactory = $configurationFactory;
        $this->configs = $configs;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConfiguration(string $name): bool
    {
        return isset($this->configurations[$name]) || $this->configs->hasConfigurationConfig($name);
    }

    /**
     * @param string $name
     *
     * @return Configuration
     *
     * @throws ConfigurationManagerException
     */
    public function getConfiguration(string $name): Configuration
    {
        if (!isset($this->configurations[$name]) && $this->configs->hasConfigurationConfig($name)) {
            $this->configurations[$name] = $this->create($name, $this->configs->getConfigurationConfig($name));
        }

        if (isset($this->configurations[$name])) {
            return $this->configurations[$name];
        }

        throw new ConfigurationManagerException(
            sprintf('Unable to find Doctrine Configuration registered with name \'%s\'', $name)
        );
    }

    /**
     * @param iterable<string, Configuration|array<mixed>> $configurations
     */
    public function setConfigurations(iterable $configurations): void
    {
        $this->configurations = [];

        foreach ($configurations as $name => $configuration) {
            if (is_array($configuration)) {
                $this->addConfigurationConfig($name, $configuration);
            } else {
                $this->setConfiguration($name, $configuration);
            }
        }
    }

    /**
     * @param string        $name
     * @param Configuration $configuration
     */
    public function setConfiguration(string $name, Configuration $configuration): void
    {
        $this->configurations[$name] = $configuration;
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function addConfigurationConfig(string $name, array $config): void
    {
        $this->configs->setConfigurationConfig($name, $config);
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     *
     * @return Configuration
     *
     * @throws ConfigurationManagerException
     */
    private function create(string $name, array $config): Configuration
    {
        try {
            return $this->configurationFactory->create($config);
        } catch (ConfigurationFactoryException $e) {
            throw new ConfigurationManagerException(
                sprintf('Failed to create doctrine configuration \'%s\': %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
