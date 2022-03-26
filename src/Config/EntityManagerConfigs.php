<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Config;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Config
 */
class EntityManagerConfigs
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
    public function hasEntityManagerConfig(string $name): bool
    {
        return isset($this->configs[$name]);
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getEntityManagerConfig(string $name): array
    {
        return $this->configs[$name] ?? [];
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setEntityManagerConfig(string $name, array $config): void
    {
        $this->configs[$name] = $config;
    }
}
