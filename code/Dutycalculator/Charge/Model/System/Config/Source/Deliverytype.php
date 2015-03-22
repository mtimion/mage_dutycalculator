<?php
/**
 * Deliverytype.php created by a.voytik.
 * Date: 12/04/2012 05:44
 */

class Dutycalculator_Charge_Model_System_Config_Source_Deliverytype
{
	protected $_options;

	public function toOptionArray()
	{
		if (!$this->_options)
		{
			$deliveryTypes = Mage::helper('dccharge')->getDeliveryTypes();
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