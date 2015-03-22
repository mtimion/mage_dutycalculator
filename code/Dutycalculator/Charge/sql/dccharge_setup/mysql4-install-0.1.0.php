<?php
/**
 * install-0.1.0.php created by a.voytik.
 * Date: 12/03/2012 06:28
 */
/* @var $installer Dutycalculator_Charge_Model_Resource_Setup */
$installer = $this;

$productAttributesSetup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$productAttributesSetup->addAttribute('catalog_product', 'dc_product_id', array('backend'		=> '',
																			 'type'            => 'int',
																			 'frontend'        => '',
																			 'input'           => 'text',
																			 'label'           => 'DutyCalculator Category',
																			 'frontend_class'  => '',
																			 'source'          => '',
																			 'required'        => false,
																			 'user_defined'    => true,
																			 'default'         => 0,
																			 'unique'          => 0,
																			 'note'            => '',
																			 'global'          => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE));

$productAttributesSetup->addAttributeToGroup('catalog_product', 'Default', 'Prices', 'dc_product_id', 100);

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'dc_order_id', 'int(11) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'dc_order_id', 'int(11) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'dc_order_id', 'int(11) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/shipment'), 'commercial_invoice_url', 'varchar(255) NULL DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/shipment'), 'packing_list_url', 'varchar(255) NULL DEFAULT NULL');

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'delivery_duty_type', "varchar(25) NOT NULL DEFAULT '".Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU."'");
$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'delivery_duty_type', "varchar(25) NOT NULL DEFAULT '".Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU."'");
$installer->getConnection()->addColumn($this->getTable('sales/quote_address'), 'delivery_duty_type', "varchar(25) NOT NULL DEFAULT '".Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU."'");
$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'delivery_duty_type', "varchar(25) NOT NULL DEFAULT '".Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU."'");
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'delivery_duty_type', "varchar(25) NOT NULL DEFAULT '".Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU."'");
$installer->getConnection()->addColumn($this->getTable('sales/shipment'), 'delivery_duty_type', "varchar(25) NOT NULL DEFAULT '".Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU."'");

$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'base_import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/quote_item'), 'import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote_item'), 'base_import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'base_import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'import_duty_tax_invoiced', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'base_import_duty_tax_invoiced', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'import_duty_tax_refunded', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'base_import_duty_tax_refunded', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/quote_address'), 'import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address'), 'base_import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'base_import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'base_import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'base_import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'base_import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'base_import_duty_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'import_duty_tax_invoiced', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'base_import_duty_tax_invoiced', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'import_duty_tax_refunded', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'base_import_duty_tax_refunded', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->addAttribute('quote_item', 'import_duty_tax', array('type' => 'static'));
$installer->addAttribute('quote_item', 'base_import_duty_tax', array('type' => 'static'));
$installer->addAttribute('invoice_item', 'import_duty_tax', array('type' => 'static'));
$installer->addAttribute('invoice_item', 'base_import_duty_tax', array('type' => 'static'));
$installer->addAttribute('creditmemo_item', 'import_duty_tax', array('type' => 'static'));
$installer->addAttribute('creditmemo_item', 'base_import_duty_tax', array('type' => 'static'));
$installer->addAttribute('order_item', 'import_duty_tax', array('type' => 'static'));
$installer->addAttribute('order_item', 'base_import_duty_tax', array('type' => 'static'));
$installer->addAttribute('order_item', 'import_duty_tax_invoiced', array('type' => 'static'));
$installer->addAttribute('order_item', 'base_import_duty_tax_invoiced', array('type' => 'static'));
$installer->addAttribute('order_item', 'import_duty_tax_refunded', array('type' => 'static'));
$installer->addAttribute('order_item', 'base_import_duty_tax_refunded', array('type' => 'static'));
$installer->addAttribute('quote', 'import_duty_tax', array('type' => 'static'));
$installer->addAttribute('quote', 'base_import_duty_tax', array('type' => 'static'));
$installer->addAttribute('order', 'import_duty_tax', array('type' => 'static'));
$installer->addAttribute('order', 'base_import_duty_tax', array('type' => 'static'));
$installer->addAttribute('order', 'import_duty_tax_invoiced', array('type' => 'static'));
$installer->addAttribute('order', 'base_import_duty_tax_invoiced', array('type' => 'static'));
$installer->addAttribute('order', 'import_duty_tax_refunded', array('type' => 'static'));
$installer->addAttribute('order', 'base_import_duty_tax_refunded', array('type' => 'static'));
$installer->addAttribute('quote_address', 'import_duty_tax', array('type' => 'static'));
$installer->addAttribute('quote_address', 'base_import_duty_tax', array('type' => 'static'));
$installer->addAttribute('invoice', 'import_duty_tax', array('type' => 'static'));
$installer->addAttribute('invoice', 'base_import_duty_tax', array('type' => 'static'));
$installer->addAttribute('shipment', 'commercial_invoice_url', array('type' => 'static'));
$installer->addAttribute('shipment', 'packing_list_url', array('type' => 'static'));
$installer->addAttribute('creditmemo', 'import_duty_tax', array('type' => 'static'));
$installer->addAttribute('creditmemo', 'base_import_duty_tax', array('type' => 'static'));

$installer->endSetup();
