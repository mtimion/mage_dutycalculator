<?php

/* @var $installer Dutycalculator_Charge_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'base_import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/quote_item'), 'import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote_item'), 'base_import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'base_import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/quote_address'), 'import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address'), 'base_import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'base_import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'base_import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'base_import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'base_import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'base_import_duty', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->addAttribute('quote_item', 'import_duty', array('type' => 'static'));
$installer->addAttribute('quote_item', 'base_import_duty', array('type' => 'static'));
$installer->addAttribute('invoice_item', 'import_duty', array('type' => 'static'));
$installer->addAttribute('invoice_item', 'base_import_duty', array('type' => 'static'));
$installer->addAttribute('creditmemo_item', 'import_duty', array('type' => 'static'));
$installer->addAttribute('creditmemo_item', 'base_import_duty', array('type' => 'static'));
$installer->addAttribute('order_item', 'import_duty', array('type' => 'static'));
$installer->addAttribute('order_item', 'base_import_duty', array('type' => 'static'));
$installer->addAttribute('quote', 'import_duty', array('type' => 'static'));
$installer->addAttribute('quote', 'base_import_duty', array('type' => 'static'));
$installer->addAttribute('order', 'import_duty', array('type' => 'static'));
$installer->addAttribute('order', 'base_import_duty', array('type' => 'static'));
$installer->addAttribute('quote_address', 'import_duty', array('type' => 'static'));
$installer->addAttribute('quote_address', 'base_import_duty', array('type' => 'static'));
$installer->addAttribute('invoice', 'import_duty', array('type' => 'static'));
$installer->addAttribute('invoice', 'base_import_duty', array('type' => 'static'));
$installer->addAttribute('creditmemo', 'import_duty', array('type' => 'static'));
$installer->addAttribute('creditmemo', 'base_import_duty', array('type' => 'static'));

$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'base_sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/quote_item'), 'sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote_item'), 'base_sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'base_sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/quote_address'), 'sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address'), 'base_sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'base_sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'base_sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'base_sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'base_sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'base_sales_tax', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->addAttribute('quote_item', 'sales_tax', array('type' => 'static'));
$installer->addAttribute('quote_item', 'base_sales_tax', array('type' => 'static'));
$installer->addAttribute('invoice_item', 'sales_tax', array('type' => 'static'));
$installer->addAttribute('invoice_item', 'base_sales_tax', array('type' => 'static'));
$installer->addAttribute('creditmemo_item', 'sales_tax', array('type' => 'static'));
$installer->addAttribute('creditmemo_item', 'base_sales_tax', array('type' => 'static'));
$installer->addAttribute('order_item', 'sales_tax', array('type' => 'static'));
$installer->addAttribute('order_item', 'base_sales_tax', array('type' => 'static'));
$installer->addAttribute('quote', 'sales_tax', array('type' => 'static'));
$installer->addAttribute('quote', 'base_sales_tax', array('type' => 'static'));
$installer->addAttribute('order', 'sales_tax', array('type' => 'static'));
$installer->addAttribute('order', 'base_sales_tax', array('type' => 'static'));
$installer->addAttribute('quote_address', 'sales_tax', array('type' => 'static'));
$installer->addAttribute('quote_address', 'base_sales_tax', array('type' => 'static'));
$installer->addAttribute('invoice', 'sales_tax', array('type' => 'static'));
$installer->addAttribute('invoice', 'base_sales_tax', array('type' => 'static'));
$installer->addAttribute('creditmemo', 'sales_tax', array('type' => 'static'));
$installer->addAttribute('creditmemo', 'base_sales_tax', array('type' => 'static'));

$installer->getConnection()->addColumn($this->getTable('sales/quote_item'), 'import_duty_rate', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'import_duty_rate', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->addAttribute('quote_item', 'import_duty_rate', array('type' => 'static'));
$installer->addAttribute('order_item', 'import_duty_rate', array('type' => 'static'));

$installer->getConnection()->addColumn($this->getTable('sales/quote_item'), 'sales_tax_rate', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'sales_tax_rate', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->addAttribute('quote_item', 'sales_tax_rate', array('type' => 'static'));
$installer->addAttribute('order_item', 'sales_tax_rate', array('type' => 'static'));

$installer->endSetup();