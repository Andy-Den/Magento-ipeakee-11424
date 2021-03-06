<?php
/**
 * @package BlueAcorn_Productpage
 * @version 0.2.0
 * @copyright Copyright (c) 2015 Blue Acorn, Inc.
 */

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'free_shipping_banner', array(
    'group' => 'Details', //will add Attribute to any group under Product Information (General, Prices, Meta Info...)
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => 'Free Shipping Banner',
    'input' => 'select',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'searchable' => false,
    'filterable' => true,
    'comparable' => false,
    'visible_on_front' => true,
    'visible_in_advanced_search' => true,
    'used_in_product_listing' => true,
    'unique' => false,
) );

$installer->endSetup();

