<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Locale\Bundle\LanguageBundle;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class Languages
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class Languages implements OptionSourceInterface
{
    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * Languages constructor.
     *
     * @param ResolverInterface $localeResolver
     */
    public function __construct(ResolverInterface $localeResolver)
    {
        $this->_localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('No export for this storeview'),
                'value' => ''
            ]
        ];

        // All 2-letter codes from available languages bundle can be used to export EffectConnect catalog
        $currentLocale  = $this->_localeResolver->getLocale();
        $locales        = (new LanguageBundle())->get($currentLocale)['Languages'];
        foreach ($locales as $locale => $label) {
            if (strlen($locale) == 2) {
                $options[] = [
                    'label' => $label,
                    'value' => $locale
                ];
            }
        }

        // Sort languages alphabetically by their label
        usort($options, function ($item1, $item2) {
            return $item1['label'] <=> $item2['label'];
        });

        return $options;
    }
}
