<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Service\Exception\ConfigurationFactoryException;
use Arp\LaminasDoctrine\Service\Exception\ConfigurationManagerException;
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
     * @var DoctrineConfig
     */
    private DoctrineConfig $config;

    /**
     * @var Configuration[]
     */
    private array $configurations = [];

    /**
     * @param ConfigurationFactoryInterface $configurationFactory
     * @param DoctrineConfig                $doctrineConfig
     */
    public function __construct(ConfigurationFactoryInterface $configurationFactory, DoctrineConfig $doctrineConfig)
    {
        $this->configurationFactory = $configurationFactory;
        $this->config = $doctrineConfig;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConfiguration(string $name): bool
    {
        return isset($this->configurations[$name]) || $this->config->hasConfigurationConfig($name);
    }

    /**
     * @param string $name
     * @param array  $config
     */
    public function addConfigurationConfig(string $name, array $config): void
    {
        $this->config->setConfigurationConfig($name, $config);
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
        if (!isset($this->configurations[$name]) && $this->config->hasConfigurationConfig($name)) {
            $this->configurations[$name] = $this->create($name, $this->config->getConfigurationConfig($name));
        }

        if (isset($this->configurations[$name])) {
            return $this->configurations[$name];
        }

        throw new ConfigurationManagerException(
            sprintf('Unable to find Doctrine Configuration registered with name \'%s\'', $name)
        );
    }

    /**
     * @param iterable $configurations
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
     * @param string $name
     * @param array  $config
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
