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

namespace ImaginationMedia\SocialOrder\Observer\Sales\Order;

use ImaginationMedia\SocialOrder\Model\Donation\Helper;
use ImaginationMedia\SocialOrder\Model\Payment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderRepository;

class AfterComplete implements ObserverInterface
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * AfterComplete constructor.
     * @param OrderRepository $orderRepository
     * @param Helper $helper
     */
    public function __construct(
        OrderRepository $orderRepository,
        Helper $helper
    ) {
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /**
         * @var $orderIds array
         */
        $orderIds = $observer->getData('order_ids');
        foreach ($orderIds as $id) {
            try {
                $order = $this->orderRepository->get($id);
                if ($order->getPayment()->getMethod() === Payment::METHOD_CODE) {
                    $this->helper->createShareableProduct($order);
                }
            } catch (\Exception $ex) {
                /**
                 * Error
                 */
            }
        }
    }
}
