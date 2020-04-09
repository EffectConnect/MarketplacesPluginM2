<?php

namespace EffectConnect\Marketplaces\Block\Adminhtml\Form\Field;

use EffectConnect\Marketplaces\Helper\ModuleHelper;
use Exception;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Version
 * @package EffectConnect\Marketplaces\Block\Adminhtml\Form\Field
 */
class Version extends Field
{
    /**
     * The GitHub endpoint URL for obtaining the latest release data.
     */
    const LATEST_RELEASE_URL    = "https://api.github.com/repos/EffectConnect/MarketplacesPluginM2/releases/latest";

    /**
     * @var string
     */
    protected $_template        = 'EffectConnect_Marketplaces::system/config/version.phtml';

    /**
     * @var ModuleHelper
     */
    protected $_moduleHelper;

    /**
     * @var object
     */
    protected $_releaseData;

    /**
     * Version constructor.
     *
     * @param Context $context
     * @param ModuleHelper $moduleHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ModuleHelper $moduleHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_moduleHelper = $moduleHelper;
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element) {
        /* Removes checkbox 'use Website', etc., after the version (normally this happens when a settings is multilevel). */
        $element
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element) {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    protected function obtainVersionData()
    {
        try {
            $ch                     = curl_init();

            curl_setopt($ch, CURLOPT_URL, static::LATEST_RELEASE_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT,'EffectConnect');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2500);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2500);

            $json                   = curl_exec($ch);

            curl_close($ch);

            $data                   = $json !== false ? json_decode($json) : false;
            $this->_releaseData     = $data !== false ? $data : [];
        } catch (Exception $e) {
            $this->_releaseData = [];
        }

        return $this->_releaseData;
    }

    /**
     * @return string
     */
    public function getVersionData()
    {
        return !isset($this->_versionData) || empty($this->_versionData) ? $this->obtainVersionData() : $this->_versionData;
    }

    /**
     * @return string
     */
    public function getCurrentVersion() {
        return $this->_moduleHelper->getVersion();
    }

    /**
     * @return string
     */
    public function getLatestVersion() {
        return isset($this->getVersionData()->tag_name) ? $this->getVersionData()->tag_name : '0.0.0';
    }

    /**
     * @return string
     */
    public function isLatestVersion() {
        return version_compare($this->getCurrentVersion(), $this->getLatestVersion(), '>=');
    }
}