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

final class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var \Closure
     */
    private \Closure $factoryWrapper;

    /**
     * @var array<mixed>
     */
    private array $defaultConfig;

    /**
     * @param array<mixed> $defaultConfig
     *
     * @noinspection ProperNullCoalescingOperatorUsageInspection
     */
    public function __construct(
        private readonly ConfigurationManagerInterface $configurationManager,
        ?callable $factory = null,
        array $defaultConfig = []
    ) {
        $this->factoryWrapper = ($factory ?? [$this, 'doCreate'])(...);
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * @param array<mixed> $config
     * @param Configuration|string|null $configuration
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
     * @param array<mixed> $config
     *
     * @throws Exception
     */
    private function doCreate(
        array $config,
        ?Configuration $configuration,
        ?EventManager $eventManager = null
    ): Connection {
        $connection = DriverManager::getConnection($config, $configuration, $eventManager);

        if (!empty($config['doctrine_type_mappings'])) {
            $platform = $connection->getDatabasePlatform();
            foreach ($config['doctrine_type_mappings'] as $databaseType => $doctrineType) {
                /** @noinspection NullPointerExceptionInspection */
                $platform->registerDoctrineTypeMapping($databaseType, $doctrineType);
            }
        }

        return $connection;
    }
}
