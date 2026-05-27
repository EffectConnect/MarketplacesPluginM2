<?php

namespace EffectConnect\Marketplaces\Console\Command;

use EffectConnect\Marketplaces\Cron\ExportOffers;
use EffectConnect\Marketplaces\Helper\FrontendStoreContextHelper;
use Exception;
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
     * @var FrontendStoreContextHelper
     */
    protected $_frontendStoreContext;

    /**
     * @param ExportOffers $exportOffers
     * @param FrontendStoreContextHelper $frontendStoreContextHelper
     */
    public function __construct(
        ExportOffers $exportOffers,
        FrontendStoreContextHelper $frontendStoreContextHelper
    ) {
        $this->exportOffers = $exportOffers;
        $this->_frontendStoreContext = $frontendStoreContextHelper;
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
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Running cron...</info>');
        $this->_frontendStoreContext->emulateAreaCode(function () {
            $this->exportOffers->execute();
        });
        $output->writeln('<info>Done.</info>');
        return Command::SUCCESS;
    }
}