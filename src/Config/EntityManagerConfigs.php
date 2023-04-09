<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Config;

class EntityManagerConfigs
{
    /**
     * @param array<mixed> $configs
     */
    public function __construct(private array $configs)
    {
    }

    public function hasEntityManagerConfig(string $name): bool
    {
        return isset($this->configs[$name]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getEntityManagerConfig(string $name): array
    {
        return $this->configs[$name] ?? [];
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setEntityManagerConfig(string $name, array $config): void
    {
        $this->configs[$name] = $config;
    }
}
