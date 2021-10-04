<?php

namespace EffectConnect\Marketplaces\Helper\Transformer;

use EffectConnect\Marketplaces\Api\ChannelMappingRepositoryInterface;
use EffectConnect\Marketplaces\Api\OrderLineRepositoryInterface;
use EffectConnect\Marketplaces\Enums\Api\FilterTag;
use EffectConnect\Marketplaces\Enums\ExternalFulfilment;
use EffectConnect\Marketplaces\Enums\FeeType;
use EffectConnect\Marketplaces\Exception\OrderImportAddAddressToQuoteFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportAddCurrencyConversionRateToCommentFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportAddCurrencyToQuoteFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportAddCustomerToQuoteFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportAddDiscountCodeToQuoteFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportAddFeesToCommentFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportAddPaymentAndShippingMethodsToQuoteFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportAddProductsToQuoteFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportCreateQuoteFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportFailedException;
use EffectConnect\Marketplaces\Exception\OrderImportRegionIdRequiredException;
use EffectConnect\Marketplaces\Exception\OrderImportSubmitQuoteFailedException;
use EffectConnect\Marketplaces\Helper\LogHelper;
use EffectConnect\Marketplaces\Helper\RegionHelper;
use EffectConnect\Marketplaces\Helper\SettingsHelper;
use EffectConnect\Marketplaces\Interfaces\ValueType;
use EffectConnect\Marketplaces\Model\ChannelMapping;
use EffectConnect\Marketplaces\Model\Connection;
use EffectConnect\PHPSdk\Core\Model\Request\OrderAddress;
use EffectConnect\PHPSdk\Core\Model\Response\BillingAddress as EffectConnectBillingAddress;
use EffectConnect\PHPSdk\Core\Model\Response\Line;
use EffectConnect\PHPSdk\Core\Model\Response\LineProductIdentifiers;
use EffectConnect\PHPSdk\Core\Model\Response\Order as EffectConnectOrder;
use EffectConnect\PHPSdk\Core\Model\Response\ShippingAddress as EffectConnectShippingAddress;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data as TaxHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Registry;
use Magento\Quote\Api\Data\CurrencyInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Item as QuoteItemModel;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\ResourceModel\Order as OrderResourceModel;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config as TaxModelConfig;

/**
 * The OrderImportTransformer obtains orders from the EffectConnect Marketplaces SDK.
 * Then it transforms the orders to a format that can be used by Magento.
 *
 * Class OrderImportTransformer
 * @package EffectConnect\Marketplaces\Helper\Transformer
 */
class OrderImportTransformer extends AbstractHelper implements ValueType
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var ChannelMappingRepositoryInterface
     */
    protected $_channelMappingRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var Emulation
     */
    protected $_appEmulation;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var QuoteResourceModel
     */
    protected $_quoteResourceModel;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var ProductResourceModel
     */
    protected $_productResourceModel;

    /**
     * @var QuoteManagement
     */
    protected $_quoteManagement;

    /**
     * @var SettingsHelper
     */
    protected $_settingsHelper;

    /**
     * @var OrderStatusCollection
     */
    protected $_orderStatusCollection;

    /**
     * @var OrderResourceModel
     */
    protected $_orderResourceModel;

    /**
     * @var GroupRepository
     */
    protected $_groupRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerInterfaceFactory;

    /**
     * @var AddressRepository
     */
    protected $_addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    protected $_addressInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var LogHelper
     */
    protected $_logHelper;

    /**
     * @var CartRepositoryInterface
     */
    protected $_cartRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var CurrencyInterface
     */
    protected $_currencyInterface;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var AddressHelper
     */
    protected $_addressHelper;

    /**
     * @var OrderLineRepositoryInterface
     */
    protected $_orderLineRepository;

    /**
     * @var OrderSender
     */
    protected $_orderSender;

    /**
     * @var TaxHelper
     */
    protected $_taxHelper;

    /**
     * @var TaxCalculationInterface
     */
    protected $_taxCalculation;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var RegionHelper
     */
    protected $_regionHelper;

    /*
     * Variables below contain information about the order to import.
     */

    /**
     * Connection to use for importing the order.
     *
     * @var Connection
     */
    protected $_connection;

    /**
     * Order to import.
     *
     * @var EffectConnectOrder
     */
    protected $_effectConnectOrder;

    /**
     * Channel mapping to use for current order import.
     *
     * @var ChannelMapping
     */
    protected $_channelMapping;

    /**
     * Storeview to import the order in.
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Comments to add to the order.
     *
     * @var array
     */
    protected $_orderComments = [];

    /**
     * OrderImportTransformer constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param ChannelMappingRepositoryInterface $channelMappingRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Emulation $appEmulation
     * @param StoreManagerInterface $storeManager
     * @param QuoteFactory $quoteFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param QuoteResourceModel $quoteResourceModel
     * @param ProductFactory $productFactory
     * @param ProductResourceModel $productResourceModel
     * @param QuoteManagement $quoteManagement
     * @param SettingsHelper $settingsHelper
     * @param OrderStatusCollection $orderStatusCollection
     * @param OrderResourceModel $orderResourceModel
     * @param GroupRepository $groupRepository
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param AddressRepository $addressRepository
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param LogHelper $logHelper
     * @param CartRepositoryInterface $cartRepositoryInterface
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param CurrencyInterface $currencyInterface
     * @param CurrencyFactory $currencyFactory
     * @param AddressHelper $addressHelper
     * @param OrderLineRepositoryInterface $orderLineRepositoryInterface
     * @param OrderSender $orderSender
     * @param TaxHelper $taxHelper
     * @param TaxCalculationInterface $taxCalculationInterface
     * @param Registry $registry
     * @param RegionHelper $regionHelper
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        ChannelMappingRepositoryInterface $channelMappingRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Emulation $appEmulation,
        StoreManagerInterface $storeManager,
        QuoteFactory $quoteFactory,
        CustomerRepositoryInterface $customerRepository,
        QuoteResourceModel $quoteResourceModel,
        ProductFactory $productFactory,
        ProductResourceModel $productResourceModel,
        QuoteManagement $quoteManagement,
        SettingsHelper $settingsHelper,
        OrderStatusCollection $orderStatusCollection,
        OrderResourceModel $orderResourceModel,
        GroupRepository $groupRepository,
        CustomerInterfaceFactory $customerInterfaceFactory,
        AddressRepository $addressRepository,
        AddressInterfaceFactory $addressInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        LogHelper $logHelper,
        CartRepositoryInterface $cartRepositoryInterface,
        ProductRepositoryInterface $productRepositoryInterface,
        CurrencyInterface $currencyInterface,
        CurrencyFactory $currencyFactory,
        AddressHelper $addressHelper,
        OrderLineRepositoryInterface $orderLineRepositoryInterface,
        OrderSender $orderSender,
        TaxHelper $taxHelper,
        TaxCalculationInterface $taxCalculationInterface,
        Registry $registry,
        RegionHelper $regionHelper
    ) {
        parent::__construct($context);
        $this->_orderRepository          = $orderRepository;
        $this->_channelMappingRepository = $channelMappingRepository;
        $this->_searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->_appEmulation             = $appEmulation;
        $this->_storeManager             = $storeManager;
        $this->_quoteFactory             = $quoteFactory;
        $this->_customerRepository       = $customerRepository;
        $this->_quoteResourceModel       = $quoteResourceModel;
        $this->_productFactory           = $productFactory;
        $this->_productResourceModel     = $productResourceModel;
        $this->_quoteManagement          = $quoteManagement;
        $this->_settingsHelper           = $settingsHelper;
        $this->_orderStatusCollection    = $orderStatusCollection;
        $this->_orderResourceModel       = $orderResourceModel;
        $this->_groupRepository          = $groupRepository;
        $this->_customerInterfaceFactory = $customerInterfaceFactory;
        $this->_addressRepository        = $addressRepository;
        $this->_addressInterfaceFactory  = $addressInterfaceFactory;
        $this->_dataObjectHelper         = $dataObjectHelper;
        $this->_logHelper                = $logHelper;
        $this->_cartRepository           = $cartRepositoryInterface;
        $this->_productRepository        = $productRepositoryInterface;
        $this->_currencyInterface        = $currencyInterface;
        $this->_currencyFactory          = $currencyFactory;
        $this->_addressHelper            = $addressHelper;
        $this->_orderLineRepository      = $orderLineRepositoryInterface;
        $this->_orderSender              = $orderSender;
        $this->_taxHelper                = $taxHelper;
        $this->_taxCalculation           = $taxCalculationInterface;
        $this->_registry                 = $registry;
        $this->_regionHelper             = $regionHelper;
    }

    /**
     * @param Connection $connection
     * @param EffectConnectOrder $effectConnectOrder
     * @return bool|OrderInterface
     * @throws OrderImportFailedException
     */
    public function importOrder(Connection $connection, EffectConnectOrder $effectConnectOrder)
    {
        $this->_orderComments = [];

        // Get channel mappings for current connection.
        $channelMappings = $this->getChannelMappingsByConnectionId($connection->getEntityId());

        // Set channel mappings for current order.
        $channelMappingForCurrentOrder = $this->_channelMappingRepository->create(); // Set default (empty) channel mapping
        foreach ($channelMappings as $channelMapping)
        {
            if ($channelMapping->getEcChannelId() == $effectConnectOrder->getChannelInfo()->getId())
            {
                $channelMappingForCurrentOrder = $channelMapping;
                break;
            }
        }

        // Needed variables for importing the order.
        $this->_channelMapping     = $channelMappingForCurrentOrder;
        $this->_connection         = $connection;
        $this->_effectConnectOrder = $effectConnectOrder;
        $this->_storeId            = $this->getStoreviewId($channelMappingForCurrentOrder);

        // Check if we need to import the order.
        if ($this->skipOrderImport()) {
            return false;
        }

        // Transform EC order to Magento order and save it to Magento.
        $result = $this->transformAndSaveOrder();
        if ($result === false) {
            throw new OrderImportFailedException(__('Order import failed.'));
        }
        return $result;
    }

    /**
     * @return bool|OrderInterface
     */
    protected function transformAndSaveOrder()
    {
        // Check if we can derive a store ID from given channel mapping.
        if ($this->_storeId === 0)
        {
            $this->_logHelper->logOrderImportFailedOnStoreId(
                $this->_connection->getEntityId(),
                $this->_channelMapping
            );
            return false;
        }

        // In case connection settings were adjusted by the client, it's possible that a connection links to a website
        // that doesn't correspond with the selected storeview in the channel mapping.
        try
        {
            $websiteId = $this->_storeManager->getStore($this->_storeId)->getWebsiteId();
            if ($websiteId != $this->_connection->getWebsiteId())
            {
                $this->_logHelper->logOrderImportFailedOnStoreIdAndWebsiteIdMatch(
                    $this->_connection->getEntityId(),
                    $this->_channelMapping
                );
                return false;
            }
        }
        catch (NoSuchEntityException $e)
        {
            return false;
        }

        // Emulate correct scope (by store) when inserting order.
        $this->_appEmulation->startEnvironmentEmulation($this->_storeId);

        try
        {
            // Create basic quote for order to insert (for reserving order ID).
            $quote = $this->createQuote();
        }
        catch(OrderImportCreateQuoteFailedException $e)
        {
            $this->_logHelper->logOrderImportFailed(
                $this->_connection->getEntityId(),
                $this->_effectConnectOrder,
                $e->getMessage()
            );
            return false;
        }

        try
        {
            // Set quote currency.
            $quote = $this->addCurrencyToQuote($quote);
        }
        catch(OrderImportAddCurrencyToQuoteFailedException $e)
        {
            $this->_logHelper->logOrderImportFailed(
                $this->_connection->getEntityId(),
                $this->_effectConnectOrder,
                $e->getMessage()
            );
            return false;
        }

        try
        {
            // Add customer to quote.
            $quote = $this->addCustomerToQuote($quote);
        }
        catch(OrderImportAddCustomerToQuoteFailedException $e)
        {
            $this->_logHelper->logOrderImportFailed(
                $this->_connection->getEntityId(),
                $this->_effectConnectOrder,
                $e->getMessage()
            );
            return false;
        }

        try
        {
            $quote = $this->addProductsToQuote($quote, $this->_effectConnectOrder->getLines());
        }
        catch(OrderImportAddProductsToQuoteFailedException $e)
        {
            $this->_logHelper->logOrderImportFailed(
                $this->_connection->getEntityId(),
                $this->_effectConnectOrder,
                $e->getMessage()
            );
            return false;
        }

        try
        {
            // Add billing address to quote.
            $quote = $this->addBillingAddressToQuote($quote);

            // Add billing address to quote.
            $quote = $this->addShippingAddressToQuote($quote);
        }
        catch(OrderImportAddAddressToQuoteFailedException $e)
        {
            $this->_logHelper->logOrderImportFailed(
                $this->_connection->getEntityId(),
                $this->_effectConnectOrder,
                $e->getMessage()
            );
            return false;
        }

        try
        {
            // Add payment and shipping method to quote.
            $quote = $this->addPaymentAndShippingMethodsToQuote($quote, $this->_effectConnectOrder);
        }
        catch(OrderImportAddPaymentAndShippingMethodsToQuoteFailedException $e)
        {
            $this->_logHelper->logOrderImportFailed(
                $this->_connection->getEntityId(),
                $this->_effectConnectOrder,
                $e->getMessage()
            );
            return false;
        }

        try
        {
            // Add discount code to quote (if set in channel mapping).
            $quote = $this->addDiscountCodeToQuote($quote);
        }
        catch (OrderImportAddDiscountCodeToQuoteFailedException $e)
        {
            // This error won't break the order import.
            $this->_logHelper->logOrderImportAddDiscountCodeFailed(
                $this->_connection->getEntityId(),
                $e->getMessage()
            );
        }

        try
        {
            // Submit the quote - in other words save the quote and create the order.
            $order = $this->submitQuote($quote);
        }
        catch(OrderImportSubmitQuoteFailedException $e)
        {
            $this->_logHelper->logOrderImportFailed(
                $this->_connection->getEntityId(),
                $this->_effectConnectOrder,
                $e->getMessage()
            );
            return false;
        }

        try
        {
            // Send email to customer if we need to.
            $this->sendOrderEmail($order);
        }
        catch(Exception $e)
        {
            // This error won't break the order import.
            $this->_logHelper->logOrderImportSendOrderEmailFailed(
                $this->_connection->getEntityId(),
                $e->getMessage()
            );
        }

        try
        {
            // Update order with EffectConnect order number and channel number, set correct order status, add order comment
            $order = $this->addStatusToOrder($order);
            $order = $this->addEffectConnectNumbersToOrder($order);
            $order = $this->addCurrencyConversionRateToComment($order, $quote->getCurrencyInformation());
            $order = $this->addFeesToComment($order, $quote->getFees());
            $order = $this->addCommentsToOrder($order);
            $order = $this->addInvoiceToOrder($order);

            // Save the updated order.
            $savedOrder = $this->_orderRepository->save($order);
        }
        catch(Exception $e)
        {
            $this->_logHelper->logOrderImportFailed(
                $this->_connection->getEntityId(),
                $this->_effectConnectOrder,
                __('Order import failed when updating order: %1.', $e->getMessage())
            );
            return false;
        }

        // End scope emulation.
        $this->_appEmulation->stopEnvironmentEmulation();

        // Log successful import of order.
        $effectConnectOrderNumber = $this->_effectConnectOrder->getIdentifiers()->getEffectConnectNumber();
        $this->_logHelper->logOrderImportSucceeded(
            $this->_connection->getEntityId(),
            $effectConnectOrderNumber,
            $savedOrder->getEntityId()
        );

        return $savedOrder;
    }

    /**
     * @return QuoteModel
     * @throws OrderImportCreateQuoteFailedException
     */
    protected function createQuote() : QuoteModel
    {
        try
        {
            /* @var QuoteModel $quote */
            $quote = $this->_quoteFactory->create();
            $quote->setEcOrder(true);
            $quote->setStoreId($this->_storeId);

            // Reserve order increment id and save the quote.
            $quote->setIsMultiShipping(false);
            $quote->setInventoryProcessed(false);
            $quote->reserveOrderId();

            // Save the quote.
            $this->_cartRepository->save($quote);
        }
        catch (Exception $e)
        {
            throw new OrderImportCreateQuoteFailedException(__('Order import failed when creating empty quote. Message: %1.', $e->getMessage()));
        }

        return $quote;
    }

    /**
     * @param QuoteModel $quote
     * @return QuoteModel
     * @throws OrderImportAddCurrencyToQuoteFailedException
     */
    public function addCurrencyToQuote(QuoteModel $quote) : QuoteModel
    {
        $sourceCurrencyCode = $this->_effectConnectOrder->getCurrency();

        try
        {
            /** @var Currency $sourceCurrency */
            $sourceCurrency         = $this->_currencyFactory->create()->load($sourceCurrencyCode);
            $destinationCurrency    = $this->_storeManager->getStore($this->_storeId)->getCurrentCurrency();

            $conversionRate         = $sourceCurrency->getRate($destinationCurrency);

            $quote->setCurrencyInformation([
                'source'        => $sourceCurrency,
                'destination'   => $destinationCurrency,
                'rate'          => $conversionRate,
            ]);
        }
        catch (Exception $e)
        {
            throw new OrderImportAddCurrencyToQuoteFailedException(__('Order import failed when trying to set currency to %1. Message: %2.', $sourceCurrencyCode, $e->getMessage()));
        }

        return $quote;
    }

    /**
     * @param QuoteModel $quote
     * @return QuoteModel
     * @throws OrderImportAddCustomerToQuoteFailedException
     */
    protected function addCustomerToQuote(QuoteModel $quote) : QuoteModel
    {
        $createCustomer = $this->_channelMapping->getCustomerCreateIncludingConfiguration($this->_storeId);

        // Create customer for order.
        if ($createCustomer)
        {
            try
            {
                $quote->setCheckoutMethod('customer');
                $customer = $this->createOrLoadCustomerByEmail();
            }
            catch (Exception $e)
            {
                throw new OrderImportAddCustomerToQuoteFailedException(__('Order import failed when creating new customer. Message: %1.', $e->getMessage()));
            }
        }
        // Don't create customer for order.
        else
        {
            // Save order to specific customer.
            if ($this->_channelMapping->getCustomerId() > 0)
            {
                try
                {
                    // Set checkout method.
                    $quote->setCheckoutMethod('customer');

                    // Try to load the customer.
                    $customer = $this->_customerRepository->getById($this->_channelMapping->getCustomerId());
                }
                catch (Exception $e)
                {
                    throw new OrderImportAddCustomerToQuoteFailedException(__('Order import failed when fetching customer to assign the order to. Message: %1.', $e->getMessage()));
                }
            }
            // Checkout as guest.
            else
            {
                // Set checkout method.
                $quote->setCustomerIsGuest(true);
                $quote->setCheckoutMethod('guest');

                // Fetch empty customer object.
                $customer = $quote->getCustomer();

                // And fill it with values from the order.
                $billingAddress = $this->_effectConnectOrder->getBillingAddress();
                $customer->setEmail($billingAddress->getEmail());
                $customer->setFirstname($billingAddress->getFirstName());
                $customer->setLastname($billingAddress->getLastName());
            }
        }

        // By now we should have a customer, otherwise we can't save the order.
        if (!$customer)
        {
            throw new OrderImportAddCustomerToQuoteFailedException(__('Order import failed when adding customer to quote.'));
        }

        try
        {
            // Assign the customer to the quote.
            $quote->setCustomer($customer);
        }
        catch (Exception $e)
        {
            throw new OrderImportAddCustomerToQuoteFailedException(__('Order import failed when assigning customer to quote. Message: %1.', $e->getMessage()));
        }

        return $quote;
    }

    /**
     * Number of products in EC order line is always 1 - Magento will automatically merge order lines in case a product was ordered multiple times.
     * We get in trouble when the prices for these products differ!
     * Example: SKU 001 was ordered once with price 1,- and ordered once with price 2,-.
     * In that case Magento will create 1 order line with amount 2 (correct) and price per piece 1 (incorrect).
     * To solve this we could create the order line with price per piece of 1,50 - the totals will be correct then.
     *
     * @param Line[] $orderLines
     * @return array
     */
    protected function recalculateDuplicateProductsWithDifferentPrices(array $orderLines) : array
    {
        $recalculatedPricesByProductId = [];

        // First group all different prices by product ID.
        $pricesByProductId = [];
        foreach ($orderLines as $orderLine) {
            $productId                       = $orderLine->getProductId();
            $price                           = $orderLine->getLineAmount();
            $pricesByProductId[$productId][] = $price;
        }

        // Now for each order line, check if we need to do a recalculation.
        foreach ($orderLines as $orderLine) {
            $productId    = $orderLine->getProductId();
            $price        = $orderLine->getLineAmount();
            $priceAverage = array_sum($pricesByProductId[$productId]) / count($pricesByProductId[$productId]);

            // Do recalculation if average price differs from order line price.
            if ($price != $priceAverage) {
                $recalculatedPricesByProductId[$productId] = $priceAverage;
            }
        }

        return $recalculatedPricesByProductId;
    }

    /**
     * @param QuoteModel $quote
     * @param Line[] $orderLines
     * @return QuoteModel
     * @throws OrderImportAddProductsToQuoteFailedException
     */
    protected function addProductsToQuote(QuoteModel $quote, array $orderLines) : QuoteModel
    {
        // Support for duplicate products with different prices is unsupported by Magento, so we recalculate these prices to average price.
        $recalculatedPricesByProductId = $this->recalculateDuplicateProductsWithDifferentPrices($orderLines);
        if ($recalculatedPricesByProductId) {
            // Add comment to order.
            $this->_orderComments[] = __('NOTE: some prices were adjusted to be able to import the import to Magento.');
        }

        // Add each order line to the quote.
        foreach ($orderLines as $orderLine)
        {
            // Create empty product model in the correct storeview.
            /* @var ProductModel $product */
            $product = $this->_productFactory->create()->setStoreId($this->_storeId);

            // Try to match the product by it's Magento ID (which we provided before in the catalog export to EffectConnect).
            /* @var LineProductIdentifiers $productIdentifiers */
            $productIdentifiers = $orderLine->getProduct();
            $productId = $productIdentifiers->getIdentifier();

            // Load the product by it's id.
            try
            {
                $product = $this->_productRepository->getById(intval($productId), false, $this->_storeId);
                $product->setSkipCheckRequiredOption(true);
            }
            catch (Exception $e)
            {
                throw new OrderImportAddProductsToQuoteFailedException(__('Order import failed when trying to load product by id %1.', $productId));
            }

            try
            {
                $currencyInformation    = $quote->getCurrencyInformation();

                /** @var Currency $sourceCurrency */
                $sourceCurrency         = $currencyInformation['source'];
                $destinationCurrency    = $currencyInformation['destination'];

                $effectConnectProductId = $orderLine->getProductId();
                $sourceAmount           = isset($recalculatedPricesByProductId[$effectConnectProductId]) ? $recalculatedPricesByProductId[$effectConnectProductId] : $orderLine->getLineAmount();
                $destinationAmount      = $sourceCurrency->convert($sourceAmount, $destinationCurrency);

                // Convert product price (always includes tax) to price without tax if catalog prices don't include tax.
                $catalogPricesIncludesTax = $this->scopeConfig->getValue(TaxModelConfig::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE, $this->_storeId);
                if (!$catalogPricesIncludesTax) {
                    $taxClassId = $product->getTaxClassId();
                    $taxRate = $this->_taxCalculation->getCalculatedRate(intval($taxClassId));
                    if ($taxRate > 0) {
                        $destinationAmount = $destinationAmount / (100 + $taxRate) * 100;
                    }
                }

                // Number of products in EC order line is always 1 - Magento will automatically merge order lines in case a product was ordered multiple times.
                $quoteItem = $quote->addProduct($product);

                // Set custom price.
                $quoteItem->setCustomPrice($destinationAmount);
                $quoteItem->setOriginalCustomPrice($destinationAmount);
                $quoteItem->getProduct()->setIsSuperMode(true);

                $product->unsSkipCheckRequiredOption();
            }
            catch (Exception $e)
            {
                throw new OrderImportAddProductsToQuoteFailedException(__('Order import failed when adding product (id: %1) to quote with message [%2].', $product->getId(), $e->getMessage()));
            }

            // $quoteItem can return a string that contains a message in case the product could not be added.
            if (!($quoteItem instanceof QuoteItemModel)) {
                throw new OrderImportAddProductsToQuoteFailedException(__('Order import failed when adding product (id: %1) to quote with message [%2].', $product->getId(), $quoteItem));
            }

            // Save match between EC order lines and Magento order lines (needed later when shipping order lines).
            $quoteItem = $this->addOrderLineIdToQuoteItem($quoteItem, $orderLine);
        }

        return $quote;
    }

    /**
     * @param QuoteItemModel $quoteItem
     * @param Line $orderLine
     * @return QuoteItemModel
     */
    protected function addOrderLineIdToQuoteItem(QuoteItemModel $quoteItem, Line $orderLine)
    {
        // Later we need to save to database which EC order line belongs to which Magento order line.
        // This is needed to know what EC order lines to ship when shipment is created in Magento.
        // For now add the EC order line ID to the quote item.
        // After the quote is saved later, the quote items get an ID which we can use to save the EC line ID.
        $ecLineIds = $quoteItem->getEcLineIds();
        if (!is_array($ecLineIds)) {
            $ecLineIds = [];
        }
        $ecLineIds[] = $orderLine->getIdentifiers()->getEffectConnectLineId();
        $quoteItem->setEcLineIds($ecLineIds);
        return $quoteItem;
    }

    /**
     * @param QuoteModel $quote
     * @return QuoteModel
     * @throws OrderImportAddAddressToQuoteFailedException
     */
    protected function addBillingAddressToQuote(QuoteModel $quote) : QuoteModel
    {
        try
        {
            // Populate quote with address data.
            $this->_dataObjectHelper->populateWithArray(
                $quote->getBillingAddress(),
                $this->convertECAddressToArray($this->_effectConnectOrder->getBillingAddress()),
                QuoteAddressInterface::class
            );
        }
        catch(Exception $e)
        {
            throw new OrderImportAddAddressToQuoteFailedException(__('Order import failed when adding billing address to quote: %1.', $e->getMessage()));
        }

        return $quote;
    }

    /**
     * @param QuoteModel $quote
     * @return QuoteModel
     * @throws OrderImportAddAddressToQuoteFailedException
     */
    protected function addShippingAddressToQuote(QuoteModel $quote) : QuoteModel
    {
        try
        {
            // Populate quote with address data.
            $this->_dataObjectHelper->populateWithArray(
                $quote->getShippingAddress(),
                $this->convertECAddressToArray($this->_effectConnectOrder->getShippingAddress()),
                QuoteAddressInterface::class
            );

            $quote->getShippingAddress()->setWeight(0);
            $quote->getShippingAddress()->setShippingAmount(0);
            $quote->getShippingAddress()->setBaseShippingAmount(0);
        }
        catch(Exception $e)
        {
            throw new OrderImportAddAddressToQuoteFailedException(__('Order import failed when adding shipping address to quote: %1.', $e->getMessage()));
        }

        return $quote;
    }

    /**
     * @param QuoteModel $quote
     * @param EffectConnectOrder $order
     * @return QuoteModel
     * @throws OrderImportAddPaymentAndShippingMethodsToQuoteFailedException
     */
    protected function addPaymentAndShippingMethodsToQuote(QuoteModel $quote, EffectConnectOrder $order) : QuoteModel
    {
        try
        {
            $shipmentMethod             = $this->_channelMapping->getShippingMethodIncludingConfiguration($this->_storeId);
            $paymentMethod              = $this->_channelMapping->getPaymentMethodIncludingConfiguration($this->_storeId);
            $currencyInformation        = $quote->getCurrencyInformation();

            /** @var Currency $sourceCurrency */
            $sourceCurrency             = $currencyInformation['source'];
            $destinationCurrency        = $currencyInformation['destination'];

            $fees                       = [];

            // Process order based fees.
            foreach ($order->getFees() as $fee) {
                if ($fee->getAmount() !== 0 && $fee->getType() !== FeeType::COMMISSION()->getValue()) {
                    $feeAmount = $sourceCurrency->convert($fee->getAmount(), $destinationCurrency);
                    if (isset($fees[$fee->getType()])) {
                        $fees[$fee->getType()] += $feeAmount;
                    } else {
                        $fees[$fee->getType()] = $feeAmount;
                    }
                }
            }

            // Process order line based fees.
            foreach ($order->getLines() as $orderLine) {
                foreach ($orderLine->getFees() as $fee) {
                    if ($fee->getAmount() !== 0 && $fee->getType() !== FeeType::COMMISSION()->getValue()) {
                        $feeAmount = $sourceCurrency->convert($fee->getAmount(), $destinationCurrency);
                        if (isset($fees[$fee->getType()])) {
                            $fees[$fee->getType()] += $feeAmount;
                        } else {
                            $fees[$fee->getType()] = $feeAmount;
                        }
                    }
                }
            }

            if (!isset($fees[FeeType::SHIPPING()->getValue()])) {
                $fees[FeeType::SHIPPING()->getValue()] = 0;
            }

            $quote->setFees($fees);

            $quote->getShippingAddress()
                ->setShippingMethod($shipmentMethod)
                ->setCollectShippingRates(true)
                ->setPaymentMethod($paymentMethod);

            $quote->getPayment()->addData(['method' => $paymentMethod]);
        }
        catch(Exception $e)
        {
            throw new OrderImportAddPaymentAndShippingMethodsToQuoteFailedException(__('Order import failed when adding shipping and payment methods to quote: %1.', $e->getMessage()));
        }

        return $quote;
    }

    /**
     * @param QuoteModel $quote
     * @return QuoteModel
     * @throws OrderImportAddDiscountCodeToQuoteFailedException
     */
    protected function addDiscountCodeToQuote(QuoteModel $quote) : QuoteModel
    {
        if ($this->_channelMapping->getDiscountCode())
        {
            $quote->setCouponCode($this->_channelMapping->getDiscountCode());

            // Set that we want to recalculate quote totals and do the actual recalculation - the coupon code will be removed by Magento if is is not valid.
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            if ($quote->getCouponCode() != $this->_channelMapping->getDiscountCode())
            {
                throw new OrderImportAddDiscountCodeToQuoteFailedException(__('Applying discount code (%1) failed when importing order.', $this->_channelMapping->getDiscountCode()));
            }
        }

        return $quote;
    }

    /**
     * @param QuoteModel $quote
     * @return OrderInterface
     * @throws OrderImportSubmitQuoteFailedException
     */
    protected function submitQuote(QuoteModel $quote) : OrderInterface
    {
        try
        {
            // Set that we want to recalculate quote totals and do the actual recalculation.
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();

            // Save the quote.
            $this->_cartRepository->save($quote);

            // When we added products to the quote, we also added the EffectConnect order line IDs to the quote items.
            // Since the quote is saved now, the items got an ID from Magento.
            // So now we can save the match between EC order lines and Magento order lines.
            // We can use this for determining which items within the order have been shipped.
            foreach ($quote->getAllItems() as $quoteItem)
            {
                foreach ($quoteItem->getEcLineIds() as $ecLineId)
                {
                    $orderLine = $this->_orderLineRepository->create();
                    $orderLine->setQuoteItemId($quoteItem->getId());
                    $orderLine->setEcOrderLineId($ecLineId);
                    $this->_orderLineRepository->save($orderLine);
                }
            }

            // Set whether we have notified the client about this order (the actual mail will be send later in the sendOrderEmail function).
            $sendEmail = $this->_channelMapping->getSendEmailsIncludingConfiguration($this->_storeId);
            $quote->setCustomerNoteNotify($sendEmail);

            // Transform the quote into an order.
            $order = $this->_quoteManagement->submit($quote);

            // Add comment to order.
            $identifiers = $this->_effectConnectOrder->getIdentifiers();
            $channelInfo = $this->_effectConnectOrder->getChannelInfo();
            $orderComment = [
                __('Order imported from EffectConnect Marketplaces'),
                __('Channel:') . ' ' . $channelInfo->getType(),
                __('Order number channel:') . ' ' . $identifiers->getChannelNumber()
            ];
            $this->_orderComments[] = implode('<br/>', $orderComment);
        }
        catch (Exception $e)
        {
            throw new OrderImportSubmitQuoteFailedException(__('Order import failed when submitting quote: %1.', $e->getMessage()));
        }

        return $order;
    }

    /**
     * @param OrderInterface $order
     */
    protected function sendOrderEmail(OrderInterface $order)
    {
        if ($this->_channelMapping->getSendEmailsIncludingConfiguration($this->_storeId)) {
            $this->_orderSender->send($order);
        }
    }

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    protected function addStatusToOrder(OrderInterface $order) : OrderInterface
    {
        $status = $this->getStatus();
        $state  = $this->getState($status);

        $order->setState($state);
        $order->setStatus($status);

        return $order;
    }

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    protected function addEffectConnectNumbersToOrder(OrderInterface $order) : OrderInterface
    {
        $orderIdentifiers = $this->_effectConnectOrder->getIdentifiers();
        $order->setEcMarketplacesIdentificationNumber($orderIdentifiers->getEffectConnectNumber());
        $order->setEcMarketplacesChannelNumber($orderIdentifiers->getChannelNumber());
        $order->setEcMarketplacesConnectionId($this->_connection->getEntityId());
        return $order;
    }

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    protected function addCommentsToOrder(OrderInterface $order) : OrderInterface
    {
        foreach($this->_orderComments as $orderComment) {
            $order->addCommentToStatusHistory(strval($orderComment), false);
        }

        return $order;
    }

    /**
     * @param OrderInterface $order
     * @param array $currencyInformation
     * @return OrderInterface
     * @throws OrderImportAddCurrencyConversionRateToCommentFailedException
     */
    protected function addCurrencyConversionRateToComment(OrderInterface $order, array $currencyInformation) : OrderInterface
    {
        try
        {
            if ($currencyInformation['source']->getCode() === $currencyInformation['destination']->getCode()) {
                return $order;
            }

            $orderComment       = [
                'Channel order was payed in currency: ' . $currencyInformation['source']->getCode() . '.',
                'Channel order was imported in currency: ' . $currencyInformation['destination']->getCode() . '.',
                'Converted (' . $currencyInformation['source']->getCode() . ' to ' . $currencyInformation['destination']->getCode() . ') at rate ' . $currencyInformation['rate'] . '.'
            ];

            $this->_orderComments[] = implode('<br/>', $orderComment);
        }
        catch (Exception $e)
        {
            throw new OrderImportAddCurrencyConversionRateToCommentFailedException(__('Order import failed when adding currency conversion rate to comment. Message: %1.', $e->getMessage()));
        }

        return $order;
    }

    /**
     * @param OrderInterface $order
     * @param array $fees
     * @return OrderInterface
     * @throws OrderImportAddFeesToCommentFailedException
     */
    protected function addFeesToComment(OrderInterface $order, array $fees) : OrderInterface
    {
        try
        {
            /** @var Currency $sourceCurrency */
            $sourceCurrency = $this->_currencyFactory->create()->load($order->getOrderCurrencyCode());

            $orderComment   = [];

            foreach ($fees as $key => $amount) {
                $orderComment[] = ucfirst($key) . ': ' . $sourceCurrency->getCurrencySymbol() . round($amount, 2);
            }

            $this->_orderComments[] = implode('<br/>', $orderComment);

        }
        catch (Exception $e)
        {
            throw new OrderImportAddFeesToCommentFailedException(__('Order import failed when adding fees to comment. Message: %1.', $e->getMessage()));
        }

        return $order;
    }

    /**
     * Capture the payment online. An invoice will automatically be prepared for the order.
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    protected function addInvoiceToOrder(OrderInterface $order) : OrderInterface
    {
        $createInvoice = boolval($this->_settingsHelper->getOrderImportCreateInvoice(SettingsHelper::SCOPE_STORE, $this->_storeId) ?? true);

        if (!$createInvoice) {
            return $order;
        }

        $order->getPayment()->capture(null); // This will prepare the invoice, but not save it yet.

        return $order;
    }

    /**
     * @param int $connectionId
     * @return ExtensibleDataInterface[]
     */
    public function getChannelMappingsByConnectionId(int $connectionId) : array
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'connection_id',
                $connectionId
            )
            ->create();
        return $this->_channelMappingRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @param string $effectConnectNumber
     * @return OrderInterface[]
     */
    protected function getOrdersByEffectConnectNumber(string $effectConnectNumber) : array
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(
                'ec_marketplaces_identification_number',
                $effectConnectNumber
            )
            ->create();
        return $this->_orderRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Get storeview id from channel mapping model and return default storeview id in case it's empty.
     *
     * @param ChannelMapping $channelMapping
     * @return int
     */
    protected function getStoreviewId(ChannelMapping $channelMapping) : int
    {
        try
        {
            // Check channel mapping to known which storeview to import order to (depends on external fulfilment).
            $channelMappingStoreviewId = intval($channelMapping->getStoreviewIdInternal());
            if ($this->orderHasExternalFulfilmentTag()) {
                $channelMappingStoreviewId = intval($channelMapping->getStoreviewIdExternal());
            }
            if ($channelMappingStoreviewId > 0) {
                return $channelMappingStoreviewId;
            }
            // Use default store within current connection.
            $website = $this->_storeManager->getWebsite($this->_connection->getWebsiteId());
            return intval($website->getDefaultStore()->getStoreId());
        }
        catch (LocalizedException $e)
        {
            return 0;
        }
    }

    /**
     * @return string
     */
    protected function getStatus() : string
    {
        // Order status was set in admin?
        $orderStatus = $this->_settingsHelper->getOrderImportOrderStatus(SettingsHelper::SCOPE_STORE, $this->_storeId);
        if ($orderStatus) {
            return $orderStatus;
        }

        return OrderModel::STATE_PROCESSING;
    }

    /**
     * @param $status
     * @return string
     */
    public function getState($status) : string
    {
        foreach ($this->_orderStatusCollection->joinStates() as $statusRecord) {
            if ($statusRecord->getStatus() == $status && $statusRecord->getState()) {
                return $statusRecord->getState();
            }
        }
        return OrderModel::STATE_PROCESSING;
    }

    /**
     * @return CustomerInterface
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     * @throws OrderImportRegionIdRequiredException
     */
    protected function createOrLoadCustomerByEmail()
    {
        // Load the customer by email address.
        $emailAddress = $this->_effectConnectOrder->getBillingAddress()->getEmail();
        try
        {
            $customer = $this->_customerRepository->get($emailAddress);
        }
        catch(NoSuchEntityException $e)
        {
            // Customer does not exist yet, create it.
            // Followed procedure (loosely) when creating customer: module-customer\Api\AccountManagementInterface.php->createAccount().

            // First get empty customer Data Transfer Object (DTO).
            /** @var CustomerInterface $customer */
            $customer = $this->_customerInterfaceFactory->create();

            // Make sure we have a storeId and websiteId and createdIn to associate this customer with.
            $customer->setStoreId($this->_storeId);
            $websiteId = $this->_storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
            $storeName = $this->_storeManager->getStore($customer->getStoreId())->getName();
            $customer->setCreatedIn($storeName);

            // Populate customer data.
            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $this->convertECAddressToArray($this->_effectConnectOrder->getBillingAddress()),
                CustomerInterface::class
            );
            $customer->setEmail($emailAddress);

            // Assign customer to customer group - this is required when creating a customer, so this field should always be filled with a valid group.
            // In case the customer group was deleted from Magento, the database is set to NULL, and $customerGroupId will then be 0 - this is a valid customer group.
            $customerGroupId = $this->_channelMapping->getCustomerGroupIdIncludingConfiguration($this->_storeId);
            $customer
                ->setDisableAutoGroupChange(1)
                ->setGroupId($customerGroupId);

            // Save the customer and re-load for it's ID.
            $savedCustomer = $this->_customerRepository->save($customer);
            $customer = $this->_customerRepository->getById($savedCustomer->getId());

            // Save billing address.
            try
            {
                /** @var AddressInterface $billingAddress */
                $billingAddress = $this->_addressInterfaceFactory->create();
                $this->_dataObjectHelper->populateWithArray(
                    $billingAddress,
                    $this->convertECAddressToArray($this->_effectConnectOrder->getBillingAddress()),
                    AddressInterface::class
                );
                $billingAddress
                    ->setCustomerId($customer->getId())
                    ->setIsDefaultBilling(true);
                $this->_addressRepository->save($billingAddress);
            }
            catch (Exception $e)
            {
                // If saving of address failed, then revert saving of customer.
                $this->_registry->unregister('isSecureArea');
                $this->_registry->register('isSecureArea', true);
                $this->_customerRepository->delete($customer);
                throw $e;
            }

            // Save shipping address.
            try
            {
                /** @var AddressInterface $shippingAddress */
                $shippingAddress = $this->_addressInterfaceFactory->create();
                $this->_dataObjectHelper->populateWithArray(
                    $shippingAddress,
                    $this->convertECAddressToArray($this->_effectConnectOrder->getShippingAddress()),
                    AddressInterface::class
                );
                $shippingAddress
                    ->setCustomerId($customer->getId())
                    ->setIsDefaultShipping(true);
                $this->_addressRepository->save($shippingAddress);
            }
            catch (Exception $e)
            {
                // If saving of address failed, then revert saving of customer.
                $this->_registry->unregister('isSecureArea');
                $this->_registry->register('isSecureArea', true);
                $this->_addressRepository->delete($billingAddress);
                $this->_customerRepository->delete($customer);
                throw $e;
            }
        }

        return $customer;
    }

    /**
     * @param EffectConnectBillingAddress|EffectConnectShippingAddress $address
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws OrderImportRegionIdRequiredException
     */
    protected function convertECAddressToArray($address) : array
    {
        // Process gender.
        $gender = null;
        if ($address->getSalutation() == OrderAddress::SALUTATION_MALE) {
            $gender = 1;
        } elseif ($address->getSalutation() == OrderAddress::SALUTATION_FEMALE) {
            $gender = 2;
        }

        // Process address.
        $addressNote                 = !empty($address->getAddressNote()) ? '(' . $address->getAddressNote() . ')' : '';
        $addressStreet               = $address->getStreet();
        $addressHouseNumber          = $address->getHouseNumber();
        $addressHouseNumberExtension = $address->getHouseNumberExtension();
        $addressHouseNumberCombined  = implode(' ', array_filter(
                [
                    $address->getHouseNumber(),
                    $address->getHouseNumberExtension()
                ]
            )
        );

        // Use Magento setting Customers > Customer Configuration > Name and Address Options > Number of Lines in a Street Address.
        $streetLinesCount = $this->_addressHelper->getStreetLines($this->_storeId);
        switch ($streetLinesCount) {
            case 1:
                $streetLine = implode(' ', array_filter(
                        [
                            $addressStreet,
                            $addressHouseNumberCombined,
                            $addressNote
                        ]
                    )
                );
                break;
            case 2:
                $streetLine = [
                    $addressStreet,
                    implode(' ', array_filter(
                            [
                                $addressHouseNumberCombined,
                                $addressNote
                            ]
                        )
                    )
                ];
                break;
            default:
                $streetLine = array_filter(
                    $addressNote ?
                        [
                            $addressStreet,
                            $addressHouseNumberCombined,
                            $addressNote
                        ] : [
                        $addressStreet,
                        $addressHouseNumber,
                        $addressHouseNumberExtension
                    ]
                );
                break;
        }

        // Get region ID out of region name
        $regionId = $this->_regionHelper->getRegionId($address->getState(), $address->getCountry());

        // Log in case region ID is required but can not be found
        if (is_null($regionId) && $this->_regionHelper->regionIdIsRequired($address->getCountry())) {
            throw new OrderImportRegionIdRequiredException(__('Region %1 was not found in the list of regions for country %2 (and region is a required field).', $address->getState(), $address->getCountry()));
        }

        return [
            'gender'     => $gender,
            'firstname'  => $address->getFirstName(),
            'lastname'   => $address->getLastName(),
            'street'     => $streetLine,
            'city'       => $address->getCity(),
            'company'    => $address->getCompany(),
            'postcode'   => $address->getZipCode(),
            'email'      => $address->getEmail(),
            'telephone'  => $address->getPhone(),
            'country_id' => $address->getCountry(),
            'region'     => $address->getState(),
            'region_id'  => $regionId
        ];
    }

    /**
     * @return bool
     * @throws OrderImportFailedException
     */
    protected function skipOrderImport() : bool
    {
        // Check if order was already imported - identify by EC order number.
        $orderIdentifiers = $this->_effectConnectOrder->getIdentifiers();
        $existingOrders = $this->getOrdersByEffectConnectNumber($orderIdentifiers->getEffectConnectNumber());
        if (count($existingOrders) != 0)
        {
            $this->_logHelper->logOrderImportAlreadyExists(
                $this->_connection->getEntityId(),
                $orderIdentifiers->getEffectConnectNumber(),
                reset($existingOrders)
            );
            throw new OrderImportFailedException(__('Order Import Already Exists'));
        }

        // Check channel mapping to known whether to import order that are externally fulfilled.
        $effectConnectOrderIsExternalFulfilled = $this->orderHasExternalFulfilmentTag();
        if (
            ($this->_channelMapping->getExternalFulfilment() == ExternalFulfilment::INTERNAL_ORDERS && $effectConnectOrderIsExternalFulfilled)
            ||
            ($this->_channelMapping->getExternalFulfilment() == ExternalFulfilment::EXTERNAL_ORDERS && !$effectConnectOrderIsExternalFulfilled)
        )
        {
            $this->_logHelper->logOrderImportSkippedByExternalFulfilment(
                $this->_connection->getEntityId(),
                $effectConnectOrderIsExternalFulfilled,
                $this->_channelMapping->getExternalFulfilment()
            );
            return true;
        }

        // No reason found for skipping the order import.
        return false;
    }

    /**
     * For currently loaded effectConnect order find out if it has a 'external_fulfilment' tag.
     * @return bool
     */
    protected function orderHasExternalFulfilmentTag() : bool
    {
        $orderHasExternalFulfilmentTag = false;
        $orderTags                     = $orderIdentifiers = $this->_effectConnectOrder->getTags();
        foreach ($orderTags as $orderTag)
        {
            if ($orderTag->getTag() == FilterTag::EXTERNAL_FULFILMENT_TAG())
            {
                $orderHasExternalFulfilmentTag = true;
                break;
            }
        }
        return $orderHasExternalFulfilmentTag;
    }
}
