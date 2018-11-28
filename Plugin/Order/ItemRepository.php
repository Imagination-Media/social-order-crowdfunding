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

namespace ImaginationMedia\SocialOrder\Plugin\Order;

use ImaginationMedia\SocialOrder\Model\Product\Type\Donation;
use Magento\Catalog\Model\ProductRepository;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\ItemRepository as Subject;

class ItemRepository
{
    /**
     * @var ProductRepository
     */
    private $productRepo;

    /**
     * ItemRepository constructor.
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepo = $productRepository;
    }

    /**
     * @param Subject $subject
     * @param OrderItemInterface $entity
     * @return OrderItemInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function beforeSave(Subject $subject, OrderItemInterface $entity)
    {
        $product = $this->productRepo->getById($entity->getProductId());
        /**
         * Force product update to reload data
         */
        if ($product->getTypeId() === Donation::TYPE_ID) {
            $product->setHasDataChanges(true);
            $this->productRepo->save($product);
        }
    }
}
