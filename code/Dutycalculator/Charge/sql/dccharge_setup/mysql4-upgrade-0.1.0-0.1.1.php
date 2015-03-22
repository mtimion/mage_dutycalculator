<?php
/**
 * mysql4-upgrade-0.1.0-0.1.1.php created by a.voytik.
 * Date: 27/04/2012 09:27
 */

/* @var $installer Dutycalculator_Charge_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'dc_order_id', 'int(11) NOT NULL DEFAULT 0');
$installer->addAttribute('creditmemo', 'dc_order_id', array('type' => 'static'));

$installer->getConnection()->addColumn($this->getTable('sales/shipment'), 'dc_order_id', 'int(11) NOT NULL DEFAULT 0');
$installer->addAttribute('shipment', 'dc_order_id', array('type' => 'static'));


$installer->endSetup();