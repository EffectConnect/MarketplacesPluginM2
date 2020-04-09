<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store;

/**
 * Class ConnectionStoreViews
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class ConnectionStoreViews implements OptionSourceInterface
{
    /**
     * @var Store
     */
    protected $_store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->_store = $store;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->_store->getWebsiteCollection() as $website) {
            foreach ($this->_store->getGroupCollection() as $group) {
                if ($website->getId() != $group->getWebsiteId()) {
                    continue;
                }
                foreach ($this->_store->getStoreCollection() as $store) {
                    if ($group->getId() != $store->getGroupId()) {
                        continue;
                    }
                    $options[] = [
                        'label'      => $group->getName() . ' > ' . $store->getName(),
                        'value'      => $store->getId(),
                        'website_id' => $website->getId(),
                    ];
                }
            }
        }

        return $options;
    }
}
