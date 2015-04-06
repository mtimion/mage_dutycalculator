<?php

class Dutycalculator_Charge_Model_System_Config_Source_Consolidatedprice_Cache
{
	public function toOptionArray()
	{
		return array(
//			array('value'=>60, 'label'=>Mage::helper('dccharge')->__('Minute')),
//			array('value'=>60*60, 'label'=>Mage::helper('dccharge')->__('Hour')),
//			array('value'=>60*60*2, 'label'=>Mage::helper('dccharge')->__('2 hours')),
//			array('value'=>60*60*6, 'label'=>Mage::helper('dccharge')->__('6 hours')),
//			array('value'=>60*60*12, 'label'=>Mage::helper('dccharge')->__('12 hours')),
			array('value'=>60*60*24, 'label'=>Mage::helper('dccharge')->__('Day')),
//			array('value'=>60*60*24*3, 'label'=>Mage::helper('dccharge')->__('3 days')),
			array('value'=>60*60*24*7, 'label'=>Mage::helper('dccharge')->__('Week')),
			array('value'=>60*60*24*30, 'label'=>Mage::helper('dccharge')->__('Month')),
			array('value'=>60*60*24*30*3, 'label'=>Mage::helper('dccharge')->__('3 months')),
			array('value'=>60*60*24*30*6, 'label'=>Mage::helper('dccharge')->__('6 months')),
			array('value'=>60*60*24*365, 'label'=>Mage::helper('dccharge')->__('Year'))
		);
	}
}