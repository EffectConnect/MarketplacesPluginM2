<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CustomerGroups
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class CustomerGroups implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    protected $_customerGroup;

    /**
     * CustomerGroups constructor.
     * @param Collection $customerGroup
     */
    public function __construct(
        Collection $customerGroup
    ) {
        $this->_customerGroup = $customerGroup;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $customerGroups = $this->_customerGroup->toOptionArray();

        return $customerGroups;
    }
}
