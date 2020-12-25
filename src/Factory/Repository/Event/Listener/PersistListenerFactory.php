<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Event\Listener;

use Arp\DoctrineEntityRepository\Persistence\Event\Listener\PersistListener;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Log\NullLogger;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Event\Listener
 */
final class PersistListenerFactory extends AbstractFactory
{
    /**
     * @var string
     */
    private string $defaultClassName = PersistListener::class;

    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return PersistListener
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PersistListener
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $className = $options['class_name'] ?? $this->defaultClassName;
        $logger = $options['logger'] ?? NullLogger::class;

        if (is_string($logger)) {
            $logger = $this->getService($container, $logger, $requestedName);
        }

        return new $className($logger);
    }
}
