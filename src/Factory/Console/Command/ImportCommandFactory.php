<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Console\Command;

use Arp\LaminasDoctrine\Console\Command\ImportCommand;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class ImportCommandFactory extends AbstractFactory
{
    /**
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): ImportCommand
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        /** @var Loader $loader */
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
