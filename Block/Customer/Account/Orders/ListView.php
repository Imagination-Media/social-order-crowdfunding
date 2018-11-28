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

namespace ImaginationMedia\SocialOrder\Block\Customer\Account\Orders;

use ImaginationMedia\SocialOrder\Model\Helper;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;

class ListView extends Template
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * ListView constructor.
     * @param Template\Context $context
     * @param Session $session
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->session = $session;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getSharedItems()
    {
        $customerId = $this->session->getCustomerId();
        return $this->helper->getSharedProductCollection($customerId);
    }
}
