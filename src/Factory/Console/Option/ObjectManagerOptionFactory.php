<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Console\Option;

use Arp\LaminasDoctrine\Console\Option\ObjectManagerOption;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Console\Option
 */
final class ObjectManagerOptionFactory extends AbstractFactory
{
    /**
     * @var string|null
     */
    private ?string $defaultObjectManagerName = null;

    /**
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return ObjectManagerOption
     *
     * @throws ServiceNotCreatedException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): ObjectManagerOption {
        try {
            return new ObjectManagerOption(
                'object-manager',
                null,
                InputOption::VALUE_REQUIRED,
                'The object manager that should be used',
                $this->defaultObjectManagerName
            );
        } catch (\Exception $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to create object manager options \'%s\': %s', $requestedName, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
