<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Config;

interface DoctrineConfigInterface
{
    public function hasConnectionConfig(string $name): bool;

    /**
     * @return array<string, mixed>
     */
    public function getConnectionConfig(string $name): array;

    /**
     * @param array<string, array<mixed>> $connectionConfigs
     */
    public function setConnectionConfigs(array $connectionConfigs): void;

    /**
     * @param array<string, mixed> $connectionConfig
     */
    public function setConnectionConfig(string $name, array $connectionConfig): void;

    public function hasEntityManagerConfig(string $name): bool;

    /**
     * @return array<string, mixed>
     */
    public function getEntityManagerConfig(string $name): array;

    /**
     * @param array<string, mixed> $config
     */
    public function setEntityManagerConfig(string $name, array $config): void;

    public function hasConfigurationConfig(string $name): bool;

    /**
     * @return array<string, mixed>
     */
    public function getConfigurationConfig(string $name): array;

    /**
     * @param array<string, mixed> $config
     */
    public function setConfigurationConfig(string $name, array $config): void;

    public function hasDriverConfig(string $name): bool;

    /**
     * @return array<string, mixed>
     */
    public function getDriverConfig(string $name): array;

    /**
     * @param array<string, mixed> $config
     */
    public function setDriverConfig(string $name, array $config): void;

    /**
     * @return array<string, mixed>
     */
    public function getEntityResolverConfig(string $name): array;

    /**
     * @param array<string, mixed> $config
     */
    public function setEntityResolverConfig(string $name, array $config): void;

    public function hasCacheConfig(string $name): bool;

    /**
     * @return array<string, mixed>
     */
    public function getCacheConfig(string $name): array;

    /**
     * @param array<string, mixed> $config
     */
    public function setCacheConfig(string $name, array $config): void;

    /**
     * @param array<string, mixed> $config
     */
    public function configure(array $config): void;
}
