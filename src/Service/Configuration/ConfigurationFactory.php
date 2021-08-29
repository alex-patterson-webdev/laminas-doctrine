<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Configuration;

use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationFactoryException;
use Doctrine\ORM\Configuration;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * @deprecated
 * Factory class for the Doctrine Configuration via the Laminas ServiceManager. This is not ideal as we treat the
 * manager as a ServiceLocator, however this class is already an abstraction that is used as an implementation detail
 * of ConfigurationManager.
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service\Configuration
 */
final class ConfigurationFactory implements ConfigurationFactoryInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    private ServiceLocatorInterface $serviceManager;

    /**
     * @param ServiceLocatorInterface $serviceManager
     */
    public function __construct(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return Configuration
     *
     * @throws ConfigurationFactoryException
     */
    public function create(array $config): Configuration
    {
        try {
            return $this->serviceManager->build(Configuration::class, $config);
        } catch (ContainerExceptionInterface $e) {
            throw new ConfigurationFactoryException(
                sprintf('Failed to create ORM Configuration: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
