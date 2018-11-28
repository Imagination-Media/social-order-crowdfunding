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

namespace ImaginationMedia\SocialOrder\Setup;

use ImaginationMedia\SocialOrder\Model\Product\Type\Donation;
use ImaginationMedia\SocialOrder\Model\Helper;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    const DONATION_AMOUNT_MIN = 'donation_amount_min';
    const DONATION_AMOUNT_MAX = 'donation_amount_max';
    const DONATION_TARGET_AMOUNT = 'donation_target_amount';

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add custom attributes
         */
        $eavSetup->addAttribute(
            'catalog_product',
            self::DONATION_AMOUNT_MIN,
            [
                'group' => 'Donation Settings',
                'type' => 'decimal',
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                'frontend' => '',
                'label' => 'Donation Amount Min Value',
                'input' => 'price',
                'class' => 'validate-number',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'donation',
                'used_in_product_listing' => true,
                'sort_order' => 102
            ]
        );

        $eavSetup->addAttribute(
            'catalog_product',
            self::DONATION_AMOUNT_MAX,
            [
                'group' => 'Donation Settings',
                'type' => 'decimal',
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                'frontend' => '',
                'label' => 'Donation Amount Max Value',
                'input' => 'price',
                'class' => 'validate-number',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'donation',
                'used_in_product_listing' => true,
                'sort_order' => 103
            ]
        );

        $eavSetup->addAttribute(
            'catalog_product',
            self::DONATION_TARGET_AMOUNT,
            [
                'group' => 'Donation Settings',
                'type' => 'decimal',
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                'frontend' => '',
                'label' => 'Donation Target Amount',
                'input' => 'price',
                'class' => 'validate-number',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'donation',
                'used_in_product_listing' => true,
                'sort_order' => 104
            ]
        );

        //associate these attributes with new product type
        $fieldList = [
            self::DONATION_AMOUNT_MIN,
            self::DONATION_AMOUNT_MAX,
            self::DONATION_TARGET_AMOUNT
        ];

        // make these attributes applicable to new product type
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to')
            );
            if (!in_array(Donation::TYPE_ID, $applyTo)) {
                $applyTo[] = Donation::TYPE_ID;
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }

        /**
         * Attributes for the shareable products
         */
        $eavSetup->addAttribute(
            Product::ENTITY,
            Helper::ATTRIBUTE_CUSTOMER_ID,
            [
                'group' => 'Donation Settings',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => __('Customer Id'),
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            Helper::ATTRIBUTE_ORDER_INCREMENT_ID,
            [
                'group' => 'Donation Settings',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => __('Order Increment ID'),
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );
    }
}
