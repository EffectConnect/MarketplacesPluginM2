<?php

namespace EffectConnect\Marketplaces\Console\Command;

use EffectConnect\Marketplaces\Cron\ImportOrders;
use EffectConnect\Marketplaces\Helper\FrontendStoreContextHelper;
use Exception;
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
     * @var FrontendStoreContextHelper
     */
    protected $_frontendStoreContext;

    /**
     * @param ImportOrders $importOrders
     * @param FrontendStoreContextHelper $frontendStoreContextHelper
     */
    public function __construct(
        ImportOrders $importOrders,
        FrontendStoreContextHelper $frontendStoreContextHelper
    ) {
        $this->importOrders = $importOrders;
        $this->_frontendStoreContext = $frontendStoreContextHelper;
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
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Running cron...</info>');
        $this->_frontendStoreContext->emulateAreaCode(function () {
            $this->importOrders->execute();
        });
        $output->writeln('<info>Done.</info>');
        return Command::SUCCESS;
    }
}