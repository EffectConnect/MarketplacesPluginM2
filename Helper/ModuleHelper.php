<?php

namespace EffectConnect\Marketplaces\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;

/**
 * This helper class helps managing and obtaining information from this module.
 *
 * Class ModuleHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class ModuleHelper extends AbstractHelper
{
    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * ModuleHelper constructor.
     *
     * @param Context $context
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Context $context,
        ModuleListInterface $moduleList
    ) {
        parent::__construct($context);
        $this->_moduleList = $moduleList;
    }

    /**
     * @return array|null
     */
    public function getModule()
    {
        return $this->_moduleList->getOne($this->_getModuleName());
    }

    /**
     * @return string
     */
    public function getVersion() : string
    {
        return strval($this->getModule()['setup_version'] ?? __('Failed retrieving plugin version.'));
    }
}
