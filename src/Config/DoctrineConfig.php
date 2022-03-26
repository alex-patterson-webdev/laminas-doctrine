<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Config;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Config
 */
class DoctrineConfig implements DoctrineConfigInterface
{
    /**
     * @var EntityManagerConfigs
     */
    private EntityManagerConfigs $entityManagerConfigs;

    /**
     * @var ConnectionConfigs
     */
    private ConnectionConfigs $connectionConfigs;

    /**
     * @var ConfigurationConfigs
     */
    private ConfigurationConfigs $configurationConfigs;

    /**
     * @var array<string, mixed>
     */
    private array $config = [];

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        EntityManagerConfigs $entityManagerConfigs,
        ConnectionConfigs $connectionConfigs,
        ConfigurationConfigs $configurationConfigs,
        array $config
    ) {
        $this->entityManagerConfigs = $entityManagerConfigs;
        $this->connectionConfigs = $connectionConfigs;
        $this->configurationConfigs = $configurationConfigs;

        $this->configure($config);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConnectionConfig(string $name): bool
    {
        return $this->connectionConfigs->hasConnectionConfig($name);
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getConnectionConfig(string $name): array
    {
        return $this->connectionConfigs->getConnectionConfig($name);
    }

    /**
     * @param array<string, array<mixed>> $connectionConfigs
     */
    public function setConnectionConfigs(array $connectionConfigs): void
    {
        foreach ($connectionConfigs as $name => $connectionConfig) {
            $this->setConnectionConfig($name, $connectionConfig);
        }
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $connectionConfig
     */
    public function setConnectionConfig(string $name, array $connectionConfig): void
    {
        $this->connectionConfigs->setConnectionConfig($name, $connectionConfig);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasEntityManagerConfig(string $name): bool
    {
        return $this->entityManagerConfigs->hasEntityManagerConfig($name);
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getEntityManagerConfig(string $name): array
    {
        return $this->entityManagerConfigs->getEntityManagerConfig($name);
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setEntityManagerConfig(string $name, array $config): void
    {
        $this->entityManagerConfigs->setEntityManagerConfig($name, $config);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConfigurationConfig(string $name): bool
    {
        return $this->configurationConfigs->hasConfigurationConfig($name);
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getConfigurationConfig(string $name): array
    {
        return $this->configurationConfigs->getConfigurationConfig($name);
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setConfigurationConfig(string $name, array $config): void
    {
        $this->configurationConfigs->setConfigurationConfig($name, $config);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasDriverConfig(string $name): bool
    {
        return isset($this->config['driver'][$name]);
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getDriverConfig(string $name): array
    {
        return $this->config['driver'][$name] ?? [];
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setDriverConfig(string $name, array $config): void
    {
        $this->config['driver'][$name] = $config;
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getEntityResolverConfig(string $name): array
    {
        return $this->config['entity_resolver'][$name] ?? [];
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setEntityResolverConfig(string $name, array $config): void
    {
        $this->config['entity_resolver'][$name] = $config;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasCacheConfig(string $name): bool
    {
        return isset($this->config['cache'][$name]);
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getCacheConfig(string $name): array
    {
        return $this->config['cache'][$name] ?? [];
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setCacheConfig(string $name, array $config): void
    {
        $this->config['cache'][$name] = $config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function configure(array $config): void
    {
        if (!empty($config['connection'])) {
            foreach ($config['connection'] as $name => $configuration) {
                $this->setConnectionConfig($name, $configuration);
            }
        }

        if (!empty($config['configuration'])) {
            foreach ($config['configuration'] as $name => $configuration) {
                $this->setConfigurationConfig($name, $configuration);
            }
        }

        if (!empty($config['entitymanager'])) {
            foreach ($config['entitymanager'] as $name => $configuration) {
                $this->setEntityManagerConfig($name, $configuration);
            }
        }

        if (!empty($config['driver'])) {
            foreach ($config['driver'] as $name => $configuration) {
                $this->setDriverConfig($name, $configuration);
            }
        }

        if (!empty($config['cache'])) {
            foreach ($config['cache'] as $name => $configuration) {
                $this->setCacheConfig($name, $configuration);
            }
        }

        if (!empty($config['entity_resolver'])) {
            foreach ($config['entity_resolver'] as $name => $configuration) {
                $this->setEntityResolverConfig($name, $configuration);
            }
        }
    }
}
