<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Event\Listener;

use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateCreatedListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateDeletedListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateTimeListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateUpdatedListener;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Event\Listener
 */
final class DateTimeListenerFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return DateTimeListener
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DateTimeListener
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $dateCreatedListener = $options['create_listener'] ?? DateCreatedListener::class;
        $dateUpdatedListener = $options['update_listener'] ?? DateUpdatedListener::class;
        $dateDeletedListener = $options['delete_listener'] ?? DateDeletedListener::class;

        return new DateTimeListener(
            is_string($dateCreatedListener)
                ? $dateCreatedListener
                : $this->getService($container, $dateCreatedListener, $requestedName),
            is_string($dateUpdatedListener)
                ? $dateUpdatedListener
                : $this->getService($container, $dateUpdatedListener, $requestedName),
            is_string($dateDeletedListener)
                ? $dateDeletedListener
                : $this->getService($container, $dateDeletedListener, $requestedName)
        );
    }
}
