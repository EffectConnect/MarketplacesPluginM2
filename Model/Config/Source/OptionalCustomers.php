<?php

namespace EffectConnect\Marketplaces\Model\Config\Source;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class OptionalCustomers
 * @package EffectConnect\Marketplaces\Model\Config\Source
 */
class OptionalCustomers implements OptionSourceInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;
    
    /**
     * @var SortOrderBuilder
     */
    protected $_sortOrderBuilder;

    /**
     * OptionalCustomers constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->_customerRepository    = $customerRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_sortOrderBuilder      = $sortOrderBuilder;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        $customersArray = [];

        // First sort customer by last name.
        $customerSortOrderLastName = $this->_sortOrderBuilder
            ->setField('lastname')
            ->setAscendingDirection()
            ->create();

        // Then sort customer by last name.
        $customerSortOrderFirstName = $this->_sortOrderBuilder
            ->setField('firstname')
            ->setAscendingDirection()
            ->create();

        // Get customer list.
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addSortOrder($customerSortOrderLastName)
            ->addSortOrder($customerSortOrderFirstName)
            ->create();
        $customers = $this->_customerRepository->getList($searchCriteria)->getItems();

        // Convert search results into key/value pair array.
        foreach ($customers as $customer)
        {
            $customersArray[] = [
                'value' => $customer->getId(),
                'label' => $customer->getLastname() . ', ' . $customer->getFirstname(),
            ];
        }

        // Prepend empty customer.
        array_unshift($customersArray, [
            'value' => ' ',
            'label' => __('Select...'),
        ]);

        return $customersArray;
    }
}
