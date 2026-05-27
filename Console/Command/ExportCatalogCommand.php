<?php

namespace EffectConnect\Marketplaces\Console\Command;

use EffectConnect\Marketplaces\Cron\ExportCatalog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCatalogCommand extends Command
{
    /**
     * @var ExportCatalog
     */
    private $exportCatalog;

    /**
     * @param ExportCatalog $exportCatalog
     */
    public function __construct(
        ExportCatalog $exportCatalog
    ) {
        $this->exportCatalog = $exportCatalog;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('effectconnect:export-catalog');
        $this->setDescription('Export catalog manually');
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
        $this->exportCatalog->execute();
        $output->writeln('<info>Done.</info>');
        return Command::SUCCESS;
    }
}