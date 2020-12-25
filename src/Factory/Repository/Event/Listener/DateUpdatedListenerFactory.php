<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Event\Listener;

use Arp\DateTime\DateTimeFactory;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateUpdatedListener;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Log\NullLogger;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Event\Listener
 */
final class DateUpdatedListenerFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return DateUpdatedListener
     *
     * @throws ServiceNotCreatedException
     * @throws \Laminas\ServiceManager\Exception\ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DateUpdatedListener
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $dateTimeFactory = $options['date_time_factory'] ?? DateTimeFactory::class;
        $logger = $options['logger'] ?? NullLogger::class;

        $dateTimeFactory = $this->getService($container, $dateTimeFactory, $requestedName);

        if (is_string($logger)) {
            $logger = $this->getService($container, $logger, $requestedName);
        }

        return new DateUpdatedListener($dateTimeFactory, $logger);
    }
}
