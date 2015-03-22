<?php

class Dutycalculator_Charge_Model_System_Config_Source_Fta
{

	public function toOptionArray()
	{
		return array(
			array('value'=>0, 'label'=>Mage::helper('dccharge')->__('Ignore')),
			array('value'=>1, 'label'=>Mage::helper('dccharge')->__('Apply if applicable'))
		);
	}
}