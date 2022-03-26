<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Config;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Config
 */
class ConfigurationConfigs
{
    /**
     * @var array<mixed>
     */
    private array $configs;

    /**
     * @var array<mixed> $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConfigurationConfig(string $name): bool
    {
        return isset($this->configs[$name]);
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getConfigurationConfig(string $name): array
    {
        return $this->configs[$name] ?? [];
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setConfigurationConfig(string $name, array $config): void
    {
        $this->configs[$name] = $config;
    }
}
