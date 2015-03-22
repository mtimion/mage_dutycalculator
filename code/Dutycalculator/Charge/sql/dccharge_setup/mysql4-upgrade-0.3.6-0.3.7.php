<?php

/* @var $installer Dutycalculator_Charge_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'delivery_duty_user_choice', 'varchar(10) DEFAULT NULL');

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'delivery_duty_user_choice', 'varchar(10) DEFAULT NULL');

$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'dc_service_fee', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote'), 'base_dc_service_fee', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'dc_service_fee', 'decimal(10, 2) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'base_dc_service_fee', 'decimal(10, 2) NOT NULL DEFAULT 0');

$installer->addAttribute('quote', 'delivery_duty_user_choice', array('type' => 'static'));

$installer->addAttribute('order', 'delivery_duty_user_choice', array('type' => 'static'));

$installer->addAttribute('quote', 'dc_service_fee', array('type' => 'static'));
$installer->addAttribute('quote', 'base_dc_service_fee', array('type' => 'static'));

$installer->addAttribute('order', 'dc_service_fee', array('type' => 'static'));
$installer->addAttribute('order', 'base_dc_service_fee', array('type' => 'static'));

$installer->endSetup();