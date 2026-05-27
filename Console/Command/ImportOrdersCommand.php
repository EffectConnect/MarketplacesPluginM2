<?php

namespace EffectConnect\Marketplaces\Console\Command;

use EffectConnect\Marketplaces\Cron\ImportOrders;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportOrdersCommand extends Command
{
    /**
     * @var ImportOrders
     */
    private $importOrders;

    /**
     * @param ImportOrders $importOrders
     */
    public function __construct(
        ImportOrders $importOrders
    ) {
        $this->importOrders = $importOrders;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('effectconnect:import-orders');
        $this->setDescription('Import orders manually');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Running cron...</info>');
        $this->importOrders->execute();
        $output->writeln('<info>Done.</info>');
        return Command::SUCCESS;
    }
}