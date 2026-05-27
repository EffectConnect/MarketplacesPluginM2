<?php

namespace EffectConnect\Marketplaces\Console\Command;

use EffectConnect\Marketplaces\Cron\ExportOffers;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportOffersCommand extends Command
{
    /**
     * @var ExportOffers
     */
    private $exportOffers;

    /**
     * @param ExportOffers $exportOffers
     */
    public function __construct(
        ExportOffers $exportOffers
    ) {
        $this->exportOffers = $exportOffers;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('effectconnect:export-offers');
        $this->setDescription('Export offers manually');
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
        $this->exportOffers->execute();
        $output->writeln('<info>Done.</info>');
        return Command::SUCCESS;
    }
}