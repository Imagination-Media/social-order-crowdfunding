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

namespace ImaginationMedia\SocialOrder\Model\Product\Type\Donation;

use ImaginationMedia\SocialOrder\Setup\InstallData;
use Magento\Catalog\Model\Product\Type\Price as ProductPrice;

class Price extends ProductPrice
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getPrice($product)
    {
        return (float)$product->getData(InstallData::DONATION_TARGET_AMOUNT);
    }

    /**
     * @param float|null $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getFinalPrice($qty, $product)
    {
        return $this->getPrice($product);
    }
}
