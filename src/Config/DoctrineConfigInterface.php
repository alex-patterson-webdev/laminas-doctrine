<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Config;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Config
 */
interface DoctrineConfigInterface
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConnectionConfig(string $name): bool;

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getConnectionConfig(string $name): array;

    /**
     * @param array<string, array<mixed>> $connectionConfigs
     */
    public function setConnectionConfigs(array $connectionConfigs): void;

    /**
     * @param string               $name
     * @param array<string, mixed> $connectionConfig
     */
    public function setConnectionConfig(string $name, array $connectionConfig): void;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasEntityManagerConfig(string $name): bool;

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getEntityManagerConfig(string $name): array;

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setEntityManagerConfig(string $name, array $config): void;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConfigurationConfig(string $name): bool;

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getConfigurationConfig(string $name): array;

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setConfigurationConfig(string $name, array $config): void;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasDriverConfig(string $name): bool;

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getDriverConfig(string $name): array;

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setDriverConfig(string $name, array $config): void;

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getEntityResolverConfig(string $name): array;

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setEntityResolverConfig(string $name, array $config): void;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasCacheConfig(string $name): bool;

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    public function getCacheConfig(string $name): array;

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setCacheConfig(string $name, array $config): void;

    /**
     * @param array<string, mixed> $config
     */
    public function configure(array $config): void;
}
