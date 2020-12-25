<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Inspired by @link https://github.com/doctrine/DoctrineFixturesBundle/issues/50
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Console\Command
 */
final class RebuildCommand extends Command
{
    /**
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this->setName('orm:schema-tool:rebuild')
            ->setDescription('Drop, Create, Update and Load database fixtures in a single command')
            ->setHelp(
                'Combines the schema-tool and fixture import commands into a single command so databases can be '
                . 'easily rebuilt with the required fixture data'
            )->addOption(
                'disable-drop',
                null,
                InputOption::VALUE_NONE,
                'Prevent the schema-tool drop command from being executed'
            )->addOption(
                'disable-update',
                null,
                InputOption::VALUE_NONE,
                'Prevent the schema-tool update command from being executed'
            )->addOption(
                'disable-import',
                null,
                InputOption::VALUE_NONE,
                'Prevent the data fixtures import command from being executed'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();
        if (null === $application) {
            throw new InvalidArgumentException('The Doctrine Application is undefined');
        }
        $output->writeln('Executing database rebuild commands');

        $autoExit = $application->isAutoExitEnabled();
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        if (!$input->getOption('disable-drop')) {
            $output->writeln('Executing orm:schema:tool:drop');

            $commandArguments = [
                'command' => 'orm:schema-tool:drop',
                '--force' => true,
            ];
            $application->run(new ArrayInput($commandArguments));
        }

        $output->writeln('Executing orm:schema:tool:update');
        $commandArguments = [
            'command' => 'orm:schema-tool:create',
        ];

        $application->run(new ArrayInput($commandArguments));

        if (!$input->getOption('disable-update')) {
            $output->writeln('Executing orm:schema:tool:update');

            $commandArguments = [
                'command' => 'orm:schema-tool:update',
                '--force' => true,
            ];
            $application->run(new ArrayInput($commandArguments));
        }

        if (!$input->getOption('disable-import')) {
            $output->writeln('Executing data-fixture:import');

            $commandArguments = [
                'command' => 'data-fixture:import',
                '--append' => true,
            ];
            $application->run(new ArrayInput($commandArguments));
        }

        $application->setAutoExit($autoExit);
        $output->writeln('Rebuild completed successfully');
        return 0;
    }
}
