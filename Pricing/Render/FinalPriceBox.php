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

namespace ImaginationMedia\SocialOrder\Pricing\Render;

use ImaginationMedia\SocialOrder\Model\Helper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Render\FinalPriceBox as RenderPrice;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\View\Element\Template;

class FinalPriceBox extends RenderPrice
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var array
     */
    protected $minMaxCache = [];

    /**
     * @var array
     */
    protected $amountsCache = [];

    /**
     * FinalPriceBox constructor.
     * @param Template\Context $context
     * @param Product $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Product $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data
        );
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function isMinEqualToMax() : bool
    {
        return $this->helper->isMinEqualToMax($this->saleableItem);
    }

    /**
     * @return float
     */
    public function getMinValue() : float
    {
        return (float)$this->helper->getMinValue($this->saleableItem);
    }

    /**
     * @return float
     */
    public function getMaxValue() : float
    {
        return (float)$this->helper->getMaxValue($this->saleableItem);
    }

    /**
     * @return float
     */
    public function getAmountToReach() : float
    {
        return (float)$this->helper->getAmountToReach($this->saleableItem);
    }

    /**
     * @param float $value
     * @param bool $includeContainer
     * @return string
     */
    public function convertAndFormatCurrency(float $value, bool $includeContainer = true) : string
    {
        return $this->helper->convertAndFormatCurrency($value, $includeContainer);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrency() : string
    {
        return $this->helper->getCurrentCurrency();
    }

    /**
     * @param float $amount
     * @return float
     */
    public function convertCurrency(float $amount) : float
    {
        return $this->helper->convertCurrency($amount);
    }

    /**
     * @return float
     */
    public function getRemainingAmount() : float
    {
        return $this->helper->getRemainingAmount($this->getProduct());
    }
}
