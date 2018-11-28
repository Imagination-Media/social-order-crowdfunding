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

namespace ImaginationMedia\SocialOrder\Ui\DataProvider\Sales\Form\Modifier\Donation;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Request\Http;

class Orders extends AbstractModifier
{
    const FIELDSET_NAME = 'donation_orders_fieldset';
    const FIELD_NAME = 'donation_orders';

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /** * @var array */
    protected $meta = [];

    /**
     * @var Http
     */
    private $http;

    /**
     * Orders constructor.
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     * @param UrlInterface $backendUrl
     * @param Http $http
     * @param Session $session
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        UrlInterface $backendUrl,
        Http $http,
        Session $session
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->backendUrl = $backendUrl;
        $this->http = $http;
        $data = $this->http->getParams();
        if (key_exists("id", $data) && (int)$data["id"] > 0) {
            $session->setCurrentDonationProduct((int)$data["id"]);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->addCustomTab();
        return $this->meta;
    }

    /**
     * Add custom tab
     */
    protected function addCustomTab()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::FIELDSET_NAME => $this->getTabConfig(),
            ]
        );
    }

    /**
     * @return array
     */
    protected function getTabConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Donation Orders'),
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'provider' => static::FORM_NAME . '.product_form_data_source',
                        'ns' => static::FORM_NAME,
                        'collapsible' => true,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => true,
                                'componentType' => 'insertListing',
                                'dataScope' => 'donation_orders_listing',
                                'externalProvider' => 'donation_orders_listing.donation_orders_listing_data_source',
                                'selectionsProvider' => 'donation_orders_listing.donation_orders_listing.product_columns.ids',
                                'ns' => 'donation_orders_listing',
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => false,
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true,
                                'imports' => [
                                    'productId' => '${ $.provider }:data.product.current_product_id'
                                ],
                                'exports' => [
                                    'productId' => '${ $.externalProvider }:params.current_product_id'
                                ],

                            ],
                        ],
                    ],
                    'children' => [],
                ],
            ],
        ];
    }
}
