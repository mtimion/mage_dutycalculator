<?php

class Dutycalculator_Charge_Model_System_Config_Source_Consolidatedprice_Option
{
	public function toOptionArray()
	{
		return array(
			array('value'=>0, 'label'=>Mage::helper('dccharge')->__('Do not include import taxes into price')),
			array('value'=>1, 'label'=>Mage::helper('dccharge')->__('Include import taxes into price'))
		);
	}
}