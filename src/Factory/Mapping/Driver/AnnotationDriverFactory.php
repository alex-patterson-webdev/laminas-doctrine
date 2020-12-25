<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Mapping\Driver;

use Arp\LaminasFactory\Exception\ServiceNotCreatedException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\IndexedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Interop\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Mapping\Driver
 */
final class AnnotationDriverFactory extends AbstractDriverFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $serviceName
     * @param array|null         $options
     *
     * @return MappingDriver
     *
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $serviceName, array $options = null): MappingDriver
    {
        $options = $options ?? $this->getOptions($container, $serviceName, $options);

        $className = $options['class'] ?? null;
        if (empty($className)) {
            throw new ServiceNotCreatedException(
                sprintf('The required \'class\' configuration option is missing for service \'%s\'', $serviceName)
            );
        }

        if (!is_a($className, MappingDriver::class, true)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The driver configuration option must resolve to a class of type \'%s\'',
                    MappingDriver::class
                )
            );
        }

        if (empty($options['reader'])) {
            $options['reader'] = AnnotationReader::class;
        }

        return new AnnotationDriver(
            $this->getReader($container, $options['reader'], $serviceName),
            $options['paths'] ?? []
        );
    }

    /**
     * @param ContainerInterface $container
     * @param string|Reader      $reader
     * @param string             $serviceName
     *
     * @return Reader
     *
     * @throws ServiceNotCreatedException
     */
    private function getReader(ContainerInterface $container, string $reader, string $serviceName): Reader
    {
        if (is_string($reader)) {
            $reader = $this->getService($container, $reader, $serviceName);
        }

        if (!$reader instanceof Reader) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The reader must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    Reader::class,
                    is_object($reader) ? get_class($reader) : gettype($reader),
                    $serviceName
                )
            );
        }

        // @todo We should allow for $cache option
        return new CachedReader(new IndexedReader($reader), new ArrayCache());
    }
}
