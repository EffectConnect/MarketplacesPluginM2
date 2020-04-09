<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManager;

/**
 * Class Websites
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class Websites implements OptionSourceInterface
{
    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * Websites constructor.
     *
     * @param StoreManager $storeManager
     */
    public function __construct(StoreManager $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($type = false)
    {
        $websites           = $this->storeManager->getWebsites();
        $return             = [];

        /** @var WebsiteInterface $website */
        foreach ($websites as $website) {
            $stockCode      = $website->getCode();
            $stockName      = $website->getName() . ' (' . $stockCode . ')';
            $return[]       = [
                'value' => $stockCode,
                'label' => $stockName
            ];
        }

        return $return;
    }
}