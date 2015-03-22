<?php
/**
 * Shippingmethods.php created by a.voytik.
 * Date: 12/04/2012 05:28
 */

class Dutycalculator_Charge_Model_System_Config_Source_Shippingmethods
{
	protected $_options;

	public function toOptionArray()
	{
		if (!$this->_options)
		{
			$carriers = Mage::getModel('shipping/config')->getActiveCarriers();
			$this->_options = array();
			foreach ($carriers as $code => $carrier)
			{
				$label = ($carrier->getConfigData('title') ? $carrier->getConfigData('title') : $code);
				$this->_options[] = array('value' => $code, 'label'=> $label);
			}
		}

		$options = $this->_options;

		return $options;
	}
}
