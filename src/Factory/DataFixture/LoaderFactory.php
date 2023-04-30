<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\DataFixture;

use Arp\LaminasDoctrine\Data\DataFixtureManager;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class LoaderFactory extends AbstractFactory
{
    /**
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): Loader
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $loader = new Loader();

        try {
            if (!empty($options['directories'])) {
                foreach ($options['directories'] as $directory) {
                    $loader->loadFromDirectory($directory);
                }
            }

            if (!empty($options['fixtures'])) {
                foreach ($this->getFixtures($container, $options['fixtures'], $requestedName) as $fixture) {
                    $loader->addFixture($fixture);
                }
            }
        } catch (ContainerExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to load doctrine data fixtures: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return $loader;
    }

    /**
     * @param array<string|FixtureInterface> $fixtures
     *
     * @return array<int, FixtureInterface>
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getFixtures(ContainerInterface $container, array $fixtures, string $requestedName): array
    {
        /** @var DataFixtureManager $dataFixtureManager */
        $dataFixtureManager = $this->getService($container, DataFixtureManager::class, $requestedName);
        $fixtureObjects = [];

        foreach ($fixtures as $fixture) {
            if (is_string($fixture)) {
                $fixture = $this->getService($dataFixtureManager, $fixture, $requestedName);
            }

            if (!$fixture instanceof FixtureInterface) {
                throw new ServiceNotCreatedException(
                    sprintf(
                        'The data fixture must be an object of type \'%s\'; \'%s\' provided in \'%s\'',
                        FixtureInterface::class,
                        (is_object($fixture) ? get_class($fixture) : gettype($fixture)),
                        $requestedName
                    )
                );
            }

            $fixtureObjects[] = $fixture;
        }

        return $fixtureObjects;
    }
}
