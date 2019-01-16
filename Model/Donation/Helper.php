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

namespace ImaginationMedia\SocialOrder\Model\Donation;

use ImaginationMedia\SocialOrder\Model\Helper as BasicHelper;
use ImaginationMedia\SocialOrder\Model\Product\Type\Donation;
use ImaginationMedia\SocialOrder\Setup\InstallData;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Helper extends BasicHelper
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var DateTime
     */
    private $datetime;

    /**
     * Helper constructor.
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param OrderItemCollectionFactory $orderItemCollectionFactory
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resourceConnection
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductFactory $productFactory
     * @param ProductRepository $productRepository
     * @param DateTime $dateTime
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        OrderItemCollectionFactory $orderItemCollectionFactory,
        CollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection,
        ProductCollectionFactory $productCollectionFactory,
        ProductFactory $productFactory,
        ProductRepository $productRepository,
        DateTime $dateTime
    ) {
        parent::__construct(
            $storeManager,
            $priceCurrency,
            $orderItemCollectionFactory,
            $collectionFactory,
            $resourceConnection,
            $productCollectionFactory
        );
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->datetime = $dateTime;
    }

    /**
     * Create shareable product from order
     * @param Order $order
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function createShareableProduct(Order $order) : bool
    {
        $total = $order->getGrandTotal();
        $productData = array(
            "sku" => "shared_" .
                str_replace(" ", "", strtolower($order->getCustomerFirstname())) . "_" .
                $this->datetime->date("d-m-y_H:i:s"),
            "name" => "Shareable Item by " . $order->getCustomerFirstname() . " " . $order->getCustomerLastname(),
            "type_id" => Donation::TYPE_ID,
            "attribute_set_id" => 4,
            "website_ids" => [1],
            "visibility" => Visibility::VISIBILITY_IN_CATALOG,
            "url_key" => $this->generateUrlKey($order),
            "stock_data" => array(
                'use_config_manage_stock' => 0,
                'manage_stock' => 0,
                'min_sale_qty' => 1,
                'max_sale_qty' => 1,
                'is_in_stock' => 1,
                'qty' => 0
            ),
            InstallData::DONATION_AMOUNT_MIN => (($total/100) * 5),
            InstallData::DONATION_AMOUNT_MAX => $total,
            InstallData::DONATION_TARGET_AMOUNT => $total,
            BasicHelper::ATTRIBUTE_CUSTOMER_ID => $order->getCustomerId(),
            BasicHelper::ATTRIBUTE_ORDER_INCREMENT_ID => $order->getIncrementId(),
            "description" => $this->getDescription($order),
            "short_description" => $this->getDescription($order)
        );
        /**
         * @var $newProduct Product
         */
        $newProduct = $this->productFactory->create();
        $newProduct->setData($productData);
        $newProduct = $this->productRepository->save($newProduct);
        return ($newProduct->getId());
    }

    /**
     * Generate url key
     * @param Order $order
     * @return string
     */
    private function generateUrlKey(Order $order) : string
    {
        $urlKey = "shareable_" . $order->getCustomerFirstname() .
            " " . $order->getCustomerLastname() .
            $this->datetime->date("d-m-y_H:i:s");
        return urlencode($urlKey);
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getDescription(Order $order) : string
    {
        $description = "";
        /**
         * @var $item OrderItem
         */
        foreach ($order->getAllVisibleItems() as $item) {
            $description .= __("Name: ") . " " . $item->getProduct()->getName() . "\n";
            $description .= __("Sku: ") . " " . $item->getProduct()->getSku() . "\n";
            $description .= __("Price: ") . " " . $item->getProduct()->getFinalPrice() . "\n";
            $description .= "\n" . "\n";
        }
        return $description;
    }
}
