<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Console\Command;

use Arp\LaminasDoctrine\Console\Command\ImportCommand;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrineFixtures\Factory\Command
 */
final class ImportCommandFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ImportCommand
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ImportCommand
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        /** @var Loader|string $loader */
        $loader = $this->getService($container, $options['loader'] ?? Loader::class, $requestedName);

        try {
            $fixtures = $loader->getFixtures();

            return new ImportCommand(
                $fixtures,
                $this->getService($container, $options['executor'] ?? ORMExecutor::class, $requestedName),
                $this->getService($container, $options['purger'] ?? ORMPurger::class, $requestedName)
            );
        } catch (\Throwable $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to load Doctrine fixtures: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
