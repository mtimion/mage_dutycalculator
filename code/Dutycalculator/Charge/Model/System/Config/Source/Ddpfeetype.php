<?php

class Dutycalculator_Charge_Model_System_Config_Source_Ddpfeetype
{
	protected $_options;

	public function toOptionArray()
	{
		if (!$this->_options)
		{
			$deliveryTypes = Mage::helper('dccharge')->getDDPFeeTypes();
			$this->_options = array();
			foreach ($deliveryTypes as $deliveryTypeKey => $deliveryTypeValue)
			{
				$this->_options[] = array('value'=> $deliveryTypeKey,
					'label'=> $deliveryTypeValue);
			}
		}
		$options = $this->_options;
		return $options;
	}
}