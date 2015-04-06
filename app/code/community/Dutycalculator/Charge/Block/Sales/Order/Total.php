<?php

class Dutycalculator_Charge_Block_Sales_Order_Total extends Mage_Core_Block_Template
{
	/**
	 * Tax configuration model
	 *
	 * @var Mage_Tax_Model_Config
	 */
	protected $_config;

	/**
	 * Initialize configuration object
	 */
	protected function _construct()
	{
		$this->_config = Mage::getSingleton('tax/config');
	}

	/**
	 * Get label cell tag properties
	 *
	 * @return string
	 */
	public function getLabelProperties()
	{
		return $this->getParentBlock()->getLabelProperties();
	}

	/**
	 * Get order store object
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder()
	{
		return $this->getParentBlock()->getOrder();
	}

	/**
	 * Get totals source object
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getSource()
	{
		return $this->getParentBlock()->getSource();
	}

	/**
	 * Get value cell tag properties
	 *
	 * @return string
	 */
	public function getValueProperties()
	{
		return $this->getParentBlock()->getValueProperties();
	}

	/**
	 * Initialize reward points totals
	 *
	 * @return Enterprise_Reward_Block_Sales_Order_Total
	 */
	public function initTotals()
	{
		$store = $this->getStore();
		$source = $this->getSource();
		$parent = $this->getParentBlock();
		$grandototal = $parent->getTotal('grand_total');

		if (!$grandototal || !(float)$source->getGrandTotal())
		{
			return $this;
		}

		try
		{
			if ($this->getOrder()->getDcOrderId() || $this->getOrder()->getFailedCalculation())
			{

				if ($this->_config->displaySalesTaxWithGrandTotal($store))
				{
					$value = $source->getImportDutyTax();
					$baseValue = $source->getBaseImportDutyTax();
					$title = ($this->getOrder()->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU) ? ($this->getOrder()->getFailedCalculation() ? 'Any import duty & taxes are paid upon delivery and are not included in the final price' : 'Estimated import duty & taxes (Not included in grand total, paid upon delivery)') : 'Import duty and taxes';
					if ($source->getDcOrderId())
					{
						$title .= ' (<a href="'. Mage::getStoreConfig('dc_charge_extension/dccharge/calculation_details_uri') . $source->getDcOrderId() .'/" target="_blank">View details</a>)';
					}
					$importDutyAndTaxes = new Varien_Object(array(
																 'code' => 'import_duty_tax',
																 'strong' => false,
																 //'label'  => Mage::helper('import_duty_taxes')->formatFee($value),
																 'label' => $title,
																 'no_escape' => true,
																 'value' => $source instanceof Mage_Sales_Model_Order_Creditmemo ? -$value : $value,
																 'base_value' => $source instanceof Mage_Sales_Model_Order_Creditmemo ? -$baseValue : $baseValue,
															));
					$grandTotal = $parent->getTotal('grand_total');
					$grandTotalIncl = $parent->getTotal('grand_total_incl');
					if ($grandTotal)
					{
						$newGrandTotalExcl = max($grandTotal->value - $source->getImportDutyTax(), 0);
						$newGrandTotalBaseExcl = max($grandTotal->base_value - $source->getBaseImportDutyTax(), 0);
						$totalExcl = new Varien_Object(array(
															'code' => 'grand_total',
															'strong' => true,
															'value' => $newGrandTotalExcl,
															'base_value' => $newGrandTotalBaseExcl,
															'label' => $grandTotal->getLabel()
													   ));
						$parent->addTotal($totalExcl, 'grand_total');

						if (!$grandTotalIncl)
						{
							$totalIncl = new Varien_Object(array(
																'code'      => 'grand_total_incl',
																'strong'    => true,
																'value'     => $this->getOrder()->getGrandTotal(),
																'base_value'=> $this->getOrder()->getBaseGrandTotal(),
																'label'     => $this->__('Grand Total (Incl.Tax)')
														   ));
							$parent->addTotal($totalIncl, 'tax');
						}
					}
				}
				else
				{
					$value = $source->getImportDutyTax();
					$baseValue = $source->getBaseImportDutyTax();
					$title = ($this->getOrder()->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU) ? ($this->getOrder()->getFailedCalculation() ? 'Any import duty & taxes are paid upon delivery and are not included in the final price' : 'Estimated import duty & taxes (Not included in grand total, paid upon delivery)') : 'Import duty and taxes';
					if ($source->getDcOrderId())
					{
						 $title .= ' (<a href="'. Mage::getStoreConfig('dc_charge_extension/dccharge/calculation_details_uri') . $source->getDcOrderId() .'/" target="_blank">View details</a>)';
					}
					$importDutyAndTaxes = new Varien_Object(array(
																 'code' => 'import_duty_tax',
																 'strong' => false,
																 //'label'  => Mage::helper('import_duty_taxes')->formatFee($value),
																 'label' => $title,
																 'no_escape' => true,
																 'value' => $source instanceof Mage_Sales_Model_Order_Creditmemo ? -$value : $value,
																 'base_value' => $source instanceof Mage_Sales_Model_Order_Creditmemo ? -$baseValue : $baseValue,
															));
				}
				$parent->addTotal($importDutyAndTaxes, $this->getAfterCondition());
			}
		}
		catch (Exception $ex)
		{

		}

		return $this;
	}
}
