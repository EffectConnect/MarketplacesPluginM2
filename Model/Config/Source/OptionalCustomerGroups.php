<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class OptionalCustomerGroups
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalCustomerGroups implements OptionSourceInterface
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

        array_unshift($customerGroups, [
            'value' => '',
            'label' => ' ',
        ]);

        return $customerGroups;
    }
}
