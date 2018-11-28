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

namespace ImaginationMedia\SocialOrder\Model\Product\Type;

use ImaginationMedia\SocialOrder\Model\Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Psr\Log\LoggerInterface;

class Donation extends AbstractType
{
    const TYPE_ID = 'donation';

    /**
     * @var Helper
     */
    private $helper;

    /**
     * Donation constructor.
     * @param ProductOption $catalogProductOption
     * @param EavConfig $eavConfig
     * @param ProductType $catalogProductType
     * @param ManagerInterface $eventManager
     * @param Database $fileStorageDb
     * @param Filesystem $filesystem
     * @param Registry $coreRegistry
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param Helper $helper
     * @param Json|null $serializer
     */
    public function __construct(
        ProductOption $catalogProductOption,
        EavConfig $eavConfig,
        ProductType $catalogProductType,
        ManagerInterface $eventManager,
        Database $fileStorageDb,
        Filesystem $filesystem,
        Registry $coreRegistry,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        Helper $helper,
        Json $serializer = null
    ) {
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $serializer
        );
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTypeSpecificData(Product $product)
    {
        // method intentionally empty
    }

    /**
     * Return an array with donation amount
     * @param Product $product
     * @param DataObject $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $options = [
            'custom_donation_amount' => $buyRequest->getCustomDonationAmount(),
        ];
        return $options;
    }

    /**
     * Check that product of this type has weight
     *
     * @return bool
     */
    public function hasWeight()
    {
        return false;
    }

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        return true;
    }

    /**
     * Check if product is available or not
     *
     * @param Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        if ($this->helper->getRemainingAmount($product) <= 0) {
            return false;
        } else {
            return parent::isSalable($product);
        }
    }
}
