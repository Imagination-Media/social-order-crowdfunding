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

namespace ImaginationMedia\SocialOrder\Ui\DataProvider\Sales\Order\Donation;

use ImaginationMedia\SocialOrder\Model\Helper;
use Magento\Backend\Model\Session;
use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var int
     */
    private $productId;

    /**
     * DataProvider constructor.
     * @param string|null $name
     * @param string|null $primaryFieldName
     * @param string|null $requestFieldName
     * @param Helper $helper
     * @param Session $session
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Helper $helper,
        Session $session,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->helper = $helper;
        $this->productId = $session->getCurrentDonationProduct();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|\Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    public function getCollection()
    {
        return $this->helper->getDonationOrders($this->productId);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $items = $this->getCollection();

        $items = $this->helper->getItemsInArray($items);

        $result = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
        return $result;
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }
}
