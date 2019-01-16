<?php

/**
 * Social Order
 *
 * This extension adds the feature to share an order with other people and also create the "donation" product type.
 *
 * @package ImaginationMedia\SocialOrder
 * @author Igor Ludgero Miura <igor@imaginationmedia.com>
 * @copyright Copyright (c) 2018 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace ImaginationMedia\SocialOrder\Model;

use ImaginationMedia\SocialOrder\Model\Product\Type\Donation;
use ImaginationMedia\SocialOrder\Setup\InstallData;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as OrderItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SortOrder;

class Helper
{
    const AMOUNT_FIELD = 'custom_donation_amount';
    const INFO_BUYER_KEY = 'info_buyRequest';

    const ATTRIBUTE_ORDER_INCREMENT_ID = 'donation_order_id';
    const ATTRIBUTE_CUSTOMER_ID = 'donation_customer_id';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var OrderItemCollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Helper constructor.
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param OrderItemCollectionFactory $orderItemCollectionFactory
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resourceConnection
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        OrderItemCollectionFactory $orderItemCollectionFactory,
        CollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Get quote item amount
     *
     * @param QuoteItem $item
     * @return float
     */
    public function getItemAmount(QuoteItem $item) : float
    {
        $buyRequest = $item->getProduct()->getCustomOption(self::INFO_BUYER_KEY);
        $buyerData = $buyRequest->getData();
        $buyerData = json_decode($buyerData["value"], true);
        return (float)$buyerData[self::AMOUNT_FIELD];
    }

    /**
     * @param Product|SaleableInterface $product
     * @return bool
     */
    public function isMinEqualToMax($product) : bool
    {
        return ((float)$product->getData(InstallData::DONATION_AMOUNT_MIN) ===
            (float)$product->getData(InstallData::DONATION_AMOUNT_MAX));
    }

    /**
     * @param Product|SaleableInterface $product
     * @return float
     */
    public function getMinValue($product) : float
    {
        return (float)$product->getData(InstallData::DONATION_AMOUNT_MIN);
    }

    /**
     * @param Product|SaleableInterface $product
     * @return float
     */
    public function getMaxValue($product) : float
    {
        $max = (float)$product->getData(InstallData::DONATION_AMOUNT_MAX);
        $remainingAmount = $this->getRemainingAmount($product);
        if ($remainingAmount === 0 || $remainingAmount > $max) {
            return $max;
        } else {
            return $remainingAmount;
        }
    }

    /**
     * @param Product|SaleableInterface $product
     * @return float
     */
    public function getAmountToReach($product) : float
    {
        return (float)$product->getData(InstallData::DONATION_TARGET_AMOUNT);
    }

    /**
     * @param float $value
     * @param bool $includeContainer
     * @return string
     */
    public function convertAndFormatCurrency(float $value, bool $includeContainer = true) : string
    {
        return $this->priceCurrency->convertAndFormat($value, $includeContainer);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrency() : string
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * @param float $amount
     * @return float
     */
    public function convertCurrency(float $amount) : float
    {
        return $this->priceCurrency->convert($amount);
    }

    /**
     * @param Product|SaleableInterface $product
     * @return float
     */
    public function getRemainingAmount($product) : float
    {
        $totalAmount = $this->getReachedAmount($product);
        return ($this->getAmountToReach($product) - $totalAmount);
    }

    /**
     * @param Product|SaleableInterface $product
     * @return float
     */
    protected function getReachedAmount($product) : float
    {
        $collection = $this->orderItemCollectionFactory->create()
            ->addFieldToFilter("product_id", $product->getId())
            ->addFieldToFilter("qty_invoiced", array('gt' => 0))
            ->addFieldToSelect(["product_id", "price", "qty_canceled", "qty_refunded", "qty_invoiced"]);
        $totalAmount = 0;
        /**
         * @var $orderItem OrderItem
         */
        foreach ($collection as $orderItem) {
            $invoicedQty = $orderItem->getQtyInvoiced();
            $qtyNotAvailable = $orderItem->getQtyCanceled() + $orderItem->getQtyRefunded();
            $invoicedQty = $invoicedQty - $qtyNotAvailable;
            if ($invoicedQty > 0) {
                $totalAmount += ($invoicedQty * $orderItem->getPrice());
            }
        }
        return $totalAmount;
    }

    /**
     * @param Product|SaleableInterface $product
     * @return float
     */
    public function getReachedPercentage($product) : float
    {
        $aux1 = ($this->getReachedAmount($product) * 100);
        $aux2 = $this->getAmountToReach($product);
        $percentage = (round($aux1 / $aux2) / 100);
        return $percentage;
    }

    /**
     * @param int $productId
     * @return OrderItemCollection
     */
    public function getDonationOrders(int $productId) : OrderItemCollection
    {
        $orderTable = $this->resourceConnection->getTableName("sales_order");
        /**
         * @var $items OrderItemCollection
         */
        $items = $collection = $this->orderItemCollectionFactory->create()
            ->addFieldToFilter("product_id", $productId)
            ->addFieldToFilter("qty_invoiced", ['gt' => 0])
            ->addFieldToSelect(["item_id", "product_id", "price", "created_at", "qty_invoiced", "order_id"]);

        /**
         * Join order table
         */
        $items->join(
            array('order' => $orderTable),
            'main_table.order_id = order.entity_id',
            array('entity_id', 'increment_id', 'customer_email', 'customer_firstname', 'customer_lastname')
        );

        return $items;
    }

    /**
     * @param OrderItemCollection $collection
     * @return array
     */
    public function getItemsInArray(OrderItemCollection $collection) : array
    {
        $data = array();
        /**
         * @var $item OrderItem
         */
        foreach ($collection as $item) {
            $data[] = $item->getData();
        }
        return $data;
    }

    /**
     * Get shared products from customer
     * @param int $customerId
     * @return ProductCollection
     */
    public function getSharedProductCollection(int $customerId) : ProductCollection
    {
        /**
         * @var $productCollection ProductCollection
         */
        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToFilter('type_id', Donation::TYPE_ID)
            ->addAttributeToFilter(self::ATTRIBUTE_CUSTOMER_ID, $customerId)
            ->addAttributeToFilter('status', 1)
            ->addAttributeToSelect(['sku', 'name', 'url_key', InstallData::DONATION_TARGET_AMOUNT, 'created_at', 'status'])
            ->setOrder("created_at", SortOrder::SORT_DESC);
        return $productCollection;
    }
}
