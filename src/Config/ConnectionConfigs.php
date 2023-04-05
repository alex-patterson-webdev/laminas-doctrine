<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Config;

class ConnectionConfigs
{
    /**
     * @param array<mixed> $configs
     */
    public function __construct(private array $configs)
    {
        $this->setConnectionConfigs($configs);
    }

    public function hasConnectionConfig(string $name): bool
    {
        return isset($this->configs[$name]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getConnectionConfig(string $name): array
    {
        return $this->configs[$name] ?? [];
    }

    /**
     * @param array<string, mixed> $connectionConfigs
     */
    public function setConnectionConfigs(array $connectionConfigs): void
    {
        foreach ($connectionConfigs as $name => $connectionConfig) {
            $this->setConnectionConfig($name, $connectionConfig);
        }
    }

    /**
     * @param array<string, mixed> $connectionConfig
     */
    public function setConnectionConfig(string $name, array $connectionConfig): void
    {
        $this->configs[$name] = $connectionConfig;
    }
}
