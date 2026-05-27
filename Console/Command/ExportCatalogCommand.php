<?php

namespace EffectConnect\Marketplaces\Console\Command;

use EffectConnect\Marketplaces\Cron\ExportCatalog;
use EffectConnect\Marketplaces\Helper\FrontendStoreContextHelper;
use Exception;
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
     * @var FrontendStoreContextHelper
     */
    protected $_frontendStoreContext;

    /**
     * @param ExportCatalog $exportCatalog
     * @param FrontendStoreContextHelper $frontendStoreContextHelper
     */
    public function __construct(
        ExportCatalog $exportCatalog,
        FrontendStoreContextHelper $frontendStoreContextHelper
    ) {
        $this->exportCatalog = $exportCatalog;
        $this->_frontendStoreContext = $frontendStoreContextHelper;
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
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Running cron...</info>');
        $this->_frontendStoreContext->emulateAreaCode(function () {
            $this->exportCatalog->execute();
        });
        $output->writeln('<info>Done.</info>');
        return Command::SUCCESS;
    }
}