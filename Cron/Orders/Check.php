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

namespace ImaginationMedia\SocialOrder\Cron\Orders;

use ImaginationMedia\SocialOrder\Model\Helper as BasicHelper;
use ImaginationMedia\SocialOrder\Model\Product\Type\Donation;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManager;

class Check
{
    /**
     * @var OrderItemCollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * Check constructor.
     * @param OrderItemCollectionFactory $orderItemCollectionFactory
     * @param ProductResource $productResource
     * @param OrderRepository $orderRepository
     * @param StoreManager $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        OrderItemCollectionFactory $orderItemCollectionFactory,
        ProductResource $productResource,
        OrderRepository $orderRepository,
        StoreManager $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender
    ) {
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->productResource = $productResource;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
    }

    public function execute()
    {
        /**
         * Check all the orders with donation products to check if they can be invoiced or not
         */
        $collection = $this->orderItemCollectionFactory->create()
            ->addFieldToSelect(["order_id", "product_id", "product_type", "qty_invoiced", "qty_refunded", "price"])
            ->addFieldToFilter("product_type", Donation::TYPE_ID)
            ->addFieldToFilter("qty_invoiced", array('gteq' => 1));
        $collection->getSelect()->group("product_id");

        $products = array();

        /**
         * @var $item OrderItem
         */
        foreach ($collection as $item) {
            $value = $this->productResource->getAttributeRawValue(
                (int)$item->getProductId(),
                BasicHelper::ATTRIBUTE_CUSTOMER_ID,
                $this->storeManager->getStore()->getId()
            );
            if (is_string($value) && $value !== "") {
                if (!key_exists($item->getProductId(), $products)) {
                    $price = (float)$item->getPrice();
                    $qty = (int)$item->getQtyInvoiced() - (int)$item->getQtyRefunded();
                    $products[$item->getProductId()] = ($price * $qty);
                } else {
                    $currentValue = $products[$item->getProductId()];
                    $price = (float)$item->getPrice();
                    $qty = (int)$item->getQtyInvoiced() - (int)$item->getQtyRefunded();
                    $finalPrice = ($price * $qty);
                    $currentValue += $finalPrice;
                    $products[$item->getProductId()] = $currentValue;
                }
            }
        }

        foreach ($products as $productId => $value) {
            $incrementId = $this->productResource->getAttributeRawValue(
                $productId,
                BasicHelper::ATTRIBUTE_ORDER_INCREMENT_ID,
                $this->storeManager->getStore()
            );
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('increment_id', $incrementId, 'eq')->create();
            $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
            /**
             * @var $order Order
             */
            if (count($orderList) > 0) {
                $order = $orderList[array_keys($orderList)[0]];
                if (!$order->isCanceled() && !$order->hasInvoices()) {
                    /**
                     * Check if is possible to invoice the order
                     */
                    if ($value >= (float)$order->getSubtotalInclTax()) {
                        /**
                         * Create invoice
                         */
                        $itemsArray = $this->prepareItems($order);
                        $invoice = $this->invoiceService->prepareInvoice($order, $itemsArray);
                        $invoice->setShippingAmount($order->getShippingAmount());
                        $invoice->setSubtotal($order->getSubtotal());
                        $invoice->setBaseSubtotal($order->getBaseSubtotal());
                        $invoice->setGrandTotal($order->getGrandTotal());
                        $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
                        $invoice->register();
                        $transactionSave = $this->transaction->addObject(
                            $invoice
                        )->addObject(
                            $invoice->getOrder()
                        );
                        $transactionSave->save();
                        $this->invoiceSender->send($invoice);
                        $order->addStatusToHistory(
                            'invoiced',
                            __('Notified customer about invoice #%1.', $invoice->getId()),
                            true
                        );
                        $this->orderRepository->save($order);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Prepare items to be invoiced
     * @param Order $order
     * @return array
     */
    private function prepareItems(Order $order) : array
    {
        $items = array();
        /**
         * @var $item OrderItem
         */
        foreach ($order->getAllVisibleItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        return $items;
    }
}
