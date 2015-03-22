<?php
/**
 * mysql4-upgrade-0.1.0-0.1.1.php created by a.voytik.
 * Date: 27/04/2012 09:27
 */

/* @var $installer Dutycalculator_Charge_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'failed_calculation', 'int(11) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'failed_calculation', 'int(11) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'failed_calculation', 'int(11) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'failed_calculation', 'int(11) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/shipment'), 'failed_calculation', 'int(11) NOT NULL DEFAULT 0');

$installer->addAttribute('quote', 'failed_calculation', array('type' => 'static'));
$installer->addAttribute('order', 'failed_calculation', array('type' => 'static'));
$installer->addAttribute('invoice', 'failed_calculation', array('type' => 'static'));
$installer->addAttribute('shipment', 'failed_calculation', array('type' => 'static'));
$installer->addAttribute('creditmemo', 'failed_calculation', array('type' => 'static'));

$installer->endSetup();