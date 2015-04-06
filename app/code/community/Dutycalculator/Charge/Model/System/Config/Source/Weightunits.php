<?php
/**
 * Allowspecificshippings.php created by a.voytik.
 * Date: 12/04/2012 05:40
 */

class Dutycalculator_Charge_Model_System_Config_Source_Weightunits
{
	protected $_options;

	public function toOptionArray()
	{
		return array(
			array('value'=>'kg', 'label'=>Mage::helper('dccharge')->__('Kilogramme (kg)')),
			array('value'=>'lb', 'label'=>Mage::helper('dccharge')->__('Pound (lb)')),
		);
	}
}
