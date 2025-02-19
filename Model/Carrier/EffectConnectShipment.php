<?php

namespace EffectConnect\Marketplaces\Model\Carrier;

use EffectConnect\Marketplaces\Helper\Transformer\OrderImportTransformer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class EffectConnectShipment
 * @package EffectConnect\Marketplaces\Model\Carrier
 */
class EffectConnectShipment extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'effectconnect_marketplaces_carrier';

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * EffectConnectShipment constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_scopeConfig       = $scopeConfig;
        $this->_rateErrorFactory  = $rateErrorFactory;
        $this->_logger            = $logger;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $data
        );
    }

    /**
     * @param RateRequest $request
     * @return bool|DataObject|Result|null
     */
    public function collectRates(RateRequest $request)
    {
        /** @var Quote $quote */
        $quote          = null;
        $ecOrder        = false;
        $fees           = [];

        /** @var Item $item */
        foreach ($request->getAllItems() as $item) {
            if ($item->getQuote()->getId() !== null && OrderImportTransformer::hasQuoteId($item->getQuote()->getId())) {
                $quote      = $item->getQuote();
                $fees       = OrderImportTransformer::getFees($item->getQuote()->getId());
                $ecOrder    = true;
                break;
            }
        }

        if (!$ecOrder || is_null($quote)) {
            return false;
        }

        /** @var Result $result */
        $result = $this->_rateResultFactory->create();

        $totalFeeAmount = array_sum($fees);
        $feesTitle      = implode(' / ', array_map(function ($feeKey) {
            return ucfirst($feeKey);
        }, array_keys($fees)));

        /** @var Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($feesTitle);

        $method->setPrice($totalFeeAmount);
        $method->setCost($totalFeeAmount);
        $result->append($method);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['effectconnect_marketplaces_carrier' => $this->getConfigData('title')];
    }
}
