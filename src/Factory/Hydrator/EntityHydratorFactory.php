<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Hydrator;

use Arp\LaminasDoctrineEntityManager\Factory\Service\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Hydrator\EntityHydrator;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\Hydrator\NamingStrategy\NamingStrategyEnabledInterface;
use Laminas\Hydrator\Strategy\StrategyEnabledInterface;
use Laminas\Hydrator\Strategy\StrategyInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Hydrator
 */
final class EntityHydratorFactory extends AbstractFactory
{
    use EntityManagerFactoryProviderTrait;

    /**
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return EntityHydrator|object
     * @throws ServiceNotCreatedException
     *
     * @throws ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null)
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName, 'hydrators');

        $entityManager = $options['entity_manager'] ?? null;
        if (null === $entityManager) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_manager\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $inflector = isset($options['inflector'])
            ? $this->getService($container, $options['inflector'], $requestedName)
            : null;

        $hydrator = new EntityHydrator(
            $this->getEntityManager($container, $entityManager, $requestedName),
            isset($options['by_value']) ? (bool)$options['by_value'] : true,
            $inflector
        );

        $namingStrategy = $options['naming_strategy'] ?? null;
        if (!empty($namingStrategy) && $hydrator instanceof NamingStrategyEnabledInterface) {
            $hydrator->setNamingStrategy($this->getService($container, $namingStrategy, $requestedName));
        }

        $strategies = $options['strategies'] ?? [];
        if (!empty($strategies) && $hydrator instanceof StrategyEnabledInterface) {
            foreach ($strategies as $name => $strategy) {
                if (is_string($strategy)) {
                    $strategy = $this->getService($container, $strategy, $requestedName);
                }
                if ($strategy instanceof StrategyInterface) {
                    $hydrator->addStrategy($name, $strategy);
                }
            }
        }

        return $hydrator;
    }
}
