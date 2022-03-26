<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Config;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Config
 */
class ConnectionConfigs
{
    /**
     * @var array<string, array>
     */
    private array $configs;

    /**
     * @param array $configs
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
    public function hasConnectionConfig(string $name): bool
    {
        return isset($this->configs[$name]);
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getConnectionConfig(string $name): array
    {
        return $this->configs[$name] ?? [];
    }

    /**
     * @param array<string, array> $connectionConfigs
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
        $this->configs[$name] = $connectionConfig;
    }
}
