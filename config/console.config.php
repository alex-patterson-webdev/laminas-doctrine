<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine;

use Arp\LaminasDoctrine\Console\Command\ImportCommand;
use Arp\LaminasDoctrine\Console\Command\RebuildCommand;
use Arp\LaminasDoctrine\Console\DoctrineApplication;
use Arp\LaminasDoctrine\Console\Helper;
use Arp\LaminasDoctrine\Console\Option\ObjectManagerOption;
use Arp\LaminasDoctrine\Factory\Console\Command\ImportCommandFactory;
use Arp\LaminasDoctrine\Factory\Console\Helper\ConnectionHelperFactory;
use Arp\LaminasDoctrine\Factory\Console\Helper\EntityManagerHelperFactory;
use Arp\LaminasDoctrine\Factory\Console\Option\ObjectManagerOptionFactory;
use Arp\LaminasSymfonyConsole\Factory\Service\ApplicationFactory;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\ORM\Tools\Console\Command;

return [
    'arp' => [
        'services' => [
            DoctrineApplication::class => [
                'name' => 'ARP Doctrine Console Application',
                'version' => '1.0.0',
                'commands' => [
                    // Custom Commands
                    RebuildCommand::class,

                    // DBAL Commands
                    ReservedWordsCommand::class,
                    RunSqlCommand::class,

                    // ORM Commands
                    Command\ClearCache\CollectionRegionCommand::class,
                    Command\ClearCache\EntityRegionCommand::class,
                    Command\ClearCache\MetadataCommand::class,
                    Command\ClearCache\QueryCommand::class,
                    Command\ClearCache\QueryRegionCommand::class,
                    Command\ClearCache\ResultCommand::class,
                    Command\SchemaTool\CreateCommand::class,
                    Command\SchemaTool\UpdateCommand::class,
                    Command\SchemaTool\DropCommand::class,
                    Command\EnsureProductionSettingsCommand::class,
                    Command\GenerateProxiesCommand::class,
                    Command\ConvertMappingCommand::class,
                    Command\RunDqlCommand::class,
                    Command\ValidateSchemaCommand::class,
                    Command\InfoCommand::class,
                    Command\MappingDescribeCommand::class,

                    // Fixtures
                    ImportCommand::class,

                ],
                'helpers' => [
                    'em' => Helper\EntityManagerHelper::class,
                    'db' => Helper\ConnectionHelper::class,
                ],
                'global_input_options' => [
                    ObjectManagerOption::class,
                ]
            ],
        ]
    ],

    'service_manager' => [
        'factories' => [
            // Application
            DoctrineApplication::class => ApplicationFactory::class,

            // Options
            ObjectManagerOption::class => ObjectManagerOptionFactory::class,
        ],
    ],

    'arp_console_command_manager' => [
        'factories' => [
            ImportCommand::class => ImportCommandFactory::class,
        ],
    ],

    'arp_console_helper_manager' => [
        'factories' => [
            Helper\ConnectionHelper::class    => ConnectionHelperFactory::class,
            Helper\EntityManagerHelper::class => EntityManagerHelperFactory::class,
        ],
    ],
];
