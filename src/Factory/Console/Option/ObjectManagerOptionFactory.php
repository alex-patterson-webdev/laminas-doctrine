<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Console\Option;

use Arp\LaminasDoctrine\Console\Option\ObjectManagerOption;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
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
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ObjectManagerOption
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ObjectManagerOption
    {
        return new ObjectManagerOption(
            'object-manager',
            null,
            InputOption::VALUE_REQUIRED,
            'The object manager that should be used',
            $this->defaultObjectManagerName
        );
    }
}
