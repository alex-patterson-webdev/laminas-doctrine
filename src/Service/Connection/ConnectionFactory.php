<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionFactoryException;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
final class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var ConfigurationManagerInterface
     */
    private ConfigurationManagerInterface $configurationManager;

    /**
     * @var \Closure
     */
    private \Closure $factoryWrapper;

    /**
     * @var array<mixed>
     */
    private array $defaultConfig;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @param callable|null                 $factory
     * @param array<mixed>                  $defaultConfig
     *
     * @noinspection ProperNullCoalescingOperatorUsageInspection [$this, 'doCreate'] is of type callable
     */
    public function __construct(
        ConfigurationManagerInterface $configurationManager,
        ?callable $factory = null,
        array $defaultConfig = []
    ) {
        $this->configurationManager = $configurationManager;
        $this->factoryWrapper = \Closure::fromCallable($factory ?? [$this, 'doCreate']);
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * Create a new connection from the provided $config
     *
     * @param array<mixed>              $config
     * @param Configuration|string|null $configuration
     * @param EventManager|null         $eventManager
     *
     * @return Connection
     *
     * @throws ConnectionFactoryException
     */
    public function create(array $config, $configuration = null, ?EventManager $eventManager = null): Connection
    {
        $config = array_replace_recursive($this->defaultConfig, $config);

        try {
            if (is_string($configuration)) {
                $configuration = $this->configurationManager->getConfiguration($configuration);
            }

            return call_user_func($this->factoryWrapper, $config, $configuration, $eventManager);
        } catch (\Exception $e) {
            throw new ConnectionFactoryException(
                sprintf('Failed to create new connection: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Default factory creation callable
     *
     * @param array<mixed>       $config
     * @param Configuration|null $configuration
     * @param EventManager|null  $eventManager
     *
     * @return Connection
     *
     * @throws Exception
     */
    private function doCreate(
        array $config,
        ?Configuration $configuration,
        ?EventManager $eventManager = null
    ): Connection {
        return DriverManager::getConnection($config, $configuration, $eventManager);
    }
}
