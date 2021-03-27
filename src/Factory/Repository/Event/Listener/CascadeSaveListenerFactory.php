<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Event\Listener;

use Arp\DoctrineEntityRepository\Persistence\CascadeSaveService;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\CascadeSaveListener;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Event\Listener
 */
final class CascadeSaveListenerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array<mixed>|null  $options
     *
     * @return CascadeSaveListener
     *
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): CascadeSaveListener {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        /** @var CascadeSaveService|string $cascadeSaveService */
        $cascadeSaveService = $options['cascade_save_service'] ?? CascadeSaveService::class;
        if (is_string($cascadeSaveService)) {
            $cascadeSaveService = $this->getService($container, $cascadeSaveService, $requestedName);
        }

        return new CascadeSaveListener($cascadeSaveService);
    }
}
