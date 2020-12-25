<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service;

use Arp\LaminasDoctrine\Service\Exception\ConfigurationFactoryException;
use Doctrine\ORM\Configuration;
use Laminas\ServiceManager\ServiceManager;

/**
 * Factory class for the Doctrine Configuration via the Laminas ServiceManager. This is not ideal as we treat the
 * manager as a ServiceLocator, however this class is already an abstraction that is used as an implementation detail
 * of ConfigurationManager.
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
final class ConfigurationFactory implements ConfigurationFactoryInterface
{
    /**
     * @var ServiceManager
     */
    private ServiceManager $serviceManager;

    /**
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param array $config
     *
     * @return Configuration
     *
     * @throws ConfigurationFactoryException
     */
    public function create(array $config): Configuration
    {
        try {
            return $this->serviceManager->build(Configuration::class, $config);
        } catch (\Throwable $e) {
            throw new ConfigurationFactoryException(
                sprintf('Failed to create ORM Configuration: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
