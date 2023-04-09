<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Command;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportCommand extends Command
{
    /**
     * @var FixtureInterface[]
     */
    private array $fixtures;

    private ORMExecutor $executor;

    private ?ORMPurger $purger;

    /**
     * @param FixtureInterface[] $fixtures
     * @param ORMExecutor        $executor
     * @param ORMPurger|null     $purger
     *
     * @throws LogicException
     */
    public function __construct(array $fixtures, ORMExecutor $executor, ?ORMPurger $purger = null)
    {
        $this->fixtures = $fixtures;
        $this->executor = $executor;
        $this->purger = $purger;

        parent::__construct();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Executing data fixtures...');

        $purger = $this->purger ?? $this->executor->getPurger();

        // 1. Remove existing data with delete SQL (default)
        // 2. Remove existing data with truncate SQL
        if ($purger instanceof ORMPurger && $input->getOption('purge-with-truncate')) {
            $output->writeln('Import has been configured to purge existing data');
            $purger->setPurgeMode(2);
            $this->executor->setPurger($purger);
        }

        $this->executor->execute(
            $this->fixtures,
            (bool)$input->getOption('append')
        );

        $output->writeln(sprintf('Completed execution of \'%d\' fixtures', count($this->fixtures)));

        return 0;
    }

    /**
     * Configure the command's options
     *
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('data-fixture:import')
            ->setDescription('Import Data Fixtures')
            ->setHelp('The import command Imports data-fixtures')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append data to existing data.');

        if (null !== $this->purger) {
            $this->addOption(
                'purge-with-truncate',
                null,
                InputOption::VALUE_NONE,
                'Truncate tables before inserting data'
            );
        }
    }
}
