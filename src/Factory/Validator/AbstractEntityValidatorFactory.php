<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Validator;

use Arp\LaminasDoctrine\Repository\RepositoryManager;
use Arp\LaminasDoctrine\Validator\AbstractEntityValidator;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractEntityValidatorFactory extends AbstractFactory
{
    /**
     * @return class-string<AbstractEntityValidator>
     */
    abstract protected function getDefaultClassName(): string;

    /**
     * @throws ServiceNotCreatedException
     * @throws InvalidServiceException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): AbstractEntityValidator {
        $options = $options ?? $this->getServiceOptions($container, $requestedName, 'validators');

        $entityName = $options['entity_name'] ?? null;
        if (null === $entityName) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_name\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $fieldNames = $options['field_names'] ?? [];
        if (empty($fieldNames)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'field_names\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $this->getService($container, RepositoryManager::class, $requestedName);

        /** @var class-string<AbstractEntityValidator> $className */
        $className = $options['class_name'] ?? $this->getDefaultClassName();
        return new $className(
            $repositoryManager->get($entityName),
            $fieldNames,
            $options
        );
    }
}
