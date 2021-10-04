<?php

namespace EffectConnect\Marketplaces\Helper;

use Exception;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\RegionFactory;

/**
 * Class RegionHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class RegionHelper
{
    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * RegionHelper constructor.
     * @param RegionFactory $regionFactory
     * @param Country $country
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(
        RegionFactory $regionFactory,
        Country $country,
        DirectoryHelper $directoryHelper
    ) {
        $this->regionFactory   = $regionFactory;
        $this->country         = $country;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * @param string $regionName
     * @param string $countryId
     * @return int|null
     */
    public function getRegionId(string $regionName, string $countryId)
    {
        $region = $this->regionFactory->create();

        // Try to load region by name
        $region->loadByName($regionName, $countryId);
        if ($region->getId() !== null) {
            return intval($region->getId());
        }

        // Try to load region by code
        $region->loadByCode($regionName, $countryId);
        if ($region->getId() !== null) {
            return intval($region->getId());
        }

        return null;
    }

    /**
     * If a country has regions defined in the database AND it has a required state/region, then the region ID is required.
     *
     * @param string $countryId
     * @return bool
     */
    public function regionIdIsRequired(string $countryId): bool
    {
        try {
            $country = $this->country->loadByCode($countryId);
        } catch (Exception $e) {
            return false;
        }

        return $country->getRegions()->count()
            && in_array($countryId, $this->directoryHelper->getCountriesWithStatesRequired());
    }
}
