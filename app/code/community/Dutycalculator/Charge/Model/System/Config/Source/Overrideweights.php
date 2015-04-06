<?php
/**
 * Allowspecificshippings.php created by a.voytik.
 * Date: 12/04/2012 05:40
 */

class Dutycalculator_Charge_Model_System_Config_Source_Overrideweights
{
	protected $_options;

	public function toOptionArray()
	{
		return array(
			array('value'=>0, 'label'=>Mage::helper('dccharge')->__('Do not override products weight')),
			array('value'=>1, 'label'=>Mage::helper('dccharge')->__('Override products weight'))
		);
	}
}
