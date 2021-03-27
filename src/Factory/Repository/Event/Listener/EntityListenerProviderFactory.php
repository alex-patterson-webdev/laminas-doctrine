<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Event\Listener;

use Arp\DoctrineEntityRepository\Constant\EntityEventName;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\ClearListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateTimeListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DeleteCollectionListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\EntityValidationListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\ExceptionListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\FlushListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\HardDeleteListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\PersistListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\SaveCollectionListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\SoftDeleteListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\TransactionListener;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Event\Listener
 */
class EntityListenerProviderFactory extends ListenerProviderFactory
{
    /**
     * @var array<string>
     */
    protected array $defaultAggregateListenerConfig = [
        EntityValidationListener::class,
        TransactionListener::class,
        ExceptionListener::class,
        DateTimeListener::class,
    ];

    /**
     * @var array<int, string>[][]
     */
    protected array $defaultListenerConfig = [
        EntityEventName::CREATE => [
            1 => [
                PersistListener::class,
                FlushListener::class,
                ClearListener::class,
            ],
        ],
        EntityEventName::UPDATE => [
            1 => [
                FlushListener::class,
                ClearListener::class,
            ],
        ],
        EntityEventName::DELETE => [
            1 => [
                SoftDeleteListener::class,
                HardDeleteListener::class,
                FlushListener::class,
                ClearListener::class,
            ],
        ],
        EntityEventName::SAVE_COLLECTION => [
            1 => [
                SaveCollectionListener::class,
            ]
        ],
        EntityEventName::DELETE_COLLECTION => [
            1 => [
                DeleteCollectionListener::class,
            ]
        ],
    ];
}
