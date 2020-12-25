<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Config;

use Arp\Container\Factory\Exception\ServiceFactoryException;
use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Config
 */
final class DoctrineConfigFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return DoctrineConfig
     *
     * @throws ServiceFactoryException
     * @throws ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $options ?? $this->getApplicationOptions($container, 'doctrine');

        if (empty($options['connection'])) {
            throw new ServiceFactoryException(
                sprintf(
                    'The required \'connection\' configuration key is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        if (empty($options['configuration'])) {
            throw new ServiceFactoryException(
                sprintf(
                    'The required \'configuration\' configuration key is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        if (empty($options['entitymanager'])) {
            throw new ServiceFactoryException(
                sprintf(
                    'The required \'entitymanager\' configuration key is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        if (empty($options['driver'])) {
            throw new ServiceFactoryException(
                sprintf(
                    'The required \'driver\' configuration key is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        return new DoctrineConfig($options);
    }
}
