<?php
/**
 * mysql4-upgrade-0.1.0-0.1.1.php created by a.voytik.
 * Date: 27/04/2012 09:27
 */

/* @var $installer Dutycalculator_Charge_Model_Resource_Setup */

$installer = $this;
$productAttributesSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$productAttributesSetup->updateAttribute('catalog_product', 'dc_product_id', array('backend_model' => 'dccharge/attribute_backend_model_category'));

$installer->endSetup();