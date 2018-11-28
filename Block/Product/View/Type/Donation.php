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

namespace ImaginationMedia\SocialOrder\Block\Product\View\Type;

use ImaginationMedia\SocialOrder\Model\Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface as UrlEncoderInterface;

class Donation extends View
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * Donation constructor.
     * @param Context $context
     * @param UrlEncoderInterface $urlEncoder
     * @param JsonEncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param ProductHelper $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlEncoderInterface $urlEncoder,
        JsonEncoderInterface $jsonEncoder,
        StringUtils $string,
        ProductHelper $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function isMinEqualToMax(): bool
    {
        return $this->helper->isMinEqualToMax($this->getProduct());
    }

    /**
     * @return float
     */
    public function getMinValue(): float
    {
        return (float)$this->helper->getMinValue($this->getProduct());
    }

    /**
     * @return float
     */
    public function getMaxValue(): float
    {
        return (float)$this->helper->getMaxValue($this->getProduct());
    }

    /**
     * @return float
     */
    public function getAmountToReach(): float
    {
        return (float)$this->helper->getAmountToReach($this->getProduct());
    }

    /**
     * @param float $value
     * @param bool $includeContainer
     * @return string
     */
    public function convertAndFormatCurrency(float $value, bool $includeContainer = true): string
    {
        return $this->helper->convertAndFormatCurrency($value, $includeContainer);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrency(): string
    {
        return $this->helper->getCurrentCurrency();
    }

    /**
     * @param float $amount
     * @return float
     */
    public function convertCurrency(float $amount): float
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

    /**
     * @return float
     */
    public function getPercentage() : float
    {
        return $this->helper->getReachedPercentage($this->getProduct());
    }
}
