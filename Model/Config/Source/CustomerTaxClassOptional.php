<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory;

class CustomerTaxClassOptional implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $taxClassCollectionFactory;

    public function __construct(
        CollectionFactory $taxClassCollectionFactory
    ) {
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
    }

    public function toOptionArray(): array
    {
        $options = [
            [
                'value' => '',
                'label' => __('-- Use default tax class --'),
            ],
        ];

        $collection = $this->taxClassCollectionFactory->create();
        $collection->addFieldToFilter('class_type', ClassModel::TAX_CLASS_TYPE_CUSTOMER);
        $collection->setOrder('class_name', 'ASC');

        foreach ($collection as $taxClass) {
            $options[] = [
                'value' => (string) $taxClass->getClassId(),
                'label' => (string) $taxClass->getClassName(),
            ];
        }

        return $options;
    }
}