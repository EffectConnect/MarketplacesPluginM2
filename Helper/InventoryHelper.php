<?php

namespace EffectConnect\Marketplaces\Helper;

if (MultiSourceInventoryChecker::msiEnabled()) {
    /**
     * Class InventoryHelper
     * @package EffectConnect\Marketplaces\Helper
     */
    class InventoryHelper extends MultiSourceInventoryHelper { }
} elseif (MultiSourceInventoryChecker::traditionalEnabled()) {
    /**
     * Class InventoryHelper
     * @package EffectConnect\Marketplaces\Helper
     */
    class InventoryHelper extends TraditionalInventoryHelper { }
} else {
    /**
     * Class InventoryHelper
     * @package EffectConnect\Marketplaces\Helper
     */
    class InventoryHelper extends BaseInventoryHelper { }
}