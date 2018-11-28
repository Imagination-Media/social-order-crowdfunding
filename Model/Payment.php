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
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Payment extends AbstractMethod
{
    const METHOD_CODE = 'socialorder';

    protected $_code = self::METHOD_CODE;

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        if ($quote->getCustomerIsGuest()) {
            return false;
        }
        if ($quote !== null) {
            /**
             * @var $item QuoteItem
             */
            foreach ($quote->getItems() as $item) {
                if ($item->getProduct()->getTypeId() === Donation::TYPE_ID) {
                    return false;
                }
            }
        }
        return parent::isAvailable($quote);
    }
}
