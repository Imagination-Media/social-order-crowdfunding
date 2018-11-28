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

namespace ImaginationMedia\SocialOrder\Observer;

use ImaginationMedia\SocialOrder\Model\Helper;
use ImaginationMedia\SocialOrder\Model\Product\Type\Donation;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

class CheckoutCartAddObserver implements ObserverInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * CheckoutCartAddObserver constructor.
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        try {
            /**
             * @var $item Item
             */
            $item = $observer->getQuoteItem();
            if ($item->getProduct()->getTypeId() === Donation::TYPE_ID) {
                $amount = $this->helper->getItemAmount($item);
                if ($amount > 0) {
                    $item->setPrice($amount);
                    $item->setBasePrice($amount);
                    $item->setCustomPrice($amount);
                    $item->setOriginalCustomPrice($amount);
                    $item->setBaseRowTotal($amount * $item->getQty());
                    $item->setRowTotal($amount * $item->getQty());
                    $item->getProduct()->setIsSuperMode(true);
                }
            }
        } catch (\Exception $ex) {
            /**
             * Error setting item property
             */
        }
    }
}
