<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Event\Listener;

use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateCreatedListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateDeletedListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateTimeListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateUpdatedListener;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Event\Listener
 */
final class DateTimeListenerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return DateTimeListener
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): DateTimeListener {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $dateCreatedListener = $options['create_listener'] ?? DateCreatedListener::class;
        $dateUpdatedListener = $options['update_listener'] ?? DateUpdatedListener::class;
        $dateDeletedListener = $options['delete_listener'] ?? DateDeletedListener::class;

        return new DateTimeListener(
            is_string($dateCreatedListener)
                ? $this->getService($container, $dateCreatedListener, $requestedName)
                : $dateCreatedListener,
            is_string($dateUpdatedListener)
                ? $this->getService($container, $dateUpdatedListener, $requestedName)
                : $dateUpdatedListener,
            is_string($dateDeletedListener)
                ? $this->getService($container, $dateDeletedListener, $requestedName)
                : $dateDeletedListener
        );
    }
}
