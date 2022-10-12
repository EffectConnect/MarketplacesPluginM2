<?php

namespace EffectConnect\Marketplaces\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

/**
 * Class for serialized array data
 */
class ShippingMethodMapping extends Value
{
    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if (is_string($value)) {
            $values = json_decode($value, true);
            if (is_array($values)) {
                $this->setValue($values);
            }
        }
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            $values = array_filter($value); // POST will include empty value for new row which need to be filtered out
            $this->setValue(json_encode($values));
        }
    }
}
