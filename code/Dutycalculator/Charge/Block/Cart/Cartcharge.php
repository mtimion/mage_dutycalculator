<?php

class Dutycalculator_Charge_Block_Cart_Cartcharge extends Mage_Checkout_Block_Cart_Abstract
{
	protected $_totalRenderers;
	protected $_defaultRenderer = 'checkout/total_default';
	protected $_totals = null;

	public function getDuty()
	{
		$result = Dutycalculator_Charge_Model_Importdutytaxes::getAmount($this->getQuote());
		if (is_array($result))
		{
			return $result['total'];
		}
		return 0;
	}

	public function isReadyForDislpay()
	{
		$helper = Mage::helper('dccharge');
		$deliveryType = Mage::getStoreConfig('dc_charge_extension/dccharge/delivery-type');
		if ($deliveryType == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU &&
			$this->getCountryFromCode() &&
			$helper->canUseForCountry($this->getCountryToCode()) &&
			$this->getQuote()->getShippingAddress()->getShippingMethod() &&
			$helper->canUseForShippingMethod($this->getQuote()->getShippingAddress()->getShippingMethod())
		)
		{
			return true;
		}
		return false;
	}

	private function getCountryFromCode()
	{
		return (Mage::getStoreConfig('shipping/origin/country_id') ? Mage::getStoreConfig('shipping/origin/country_id') : Mage::getStoreConfig('general/country/default'));
	}

	private function getCountryToCode()
	{
		return $this->getQuote()->getShippingAddress()->getCountryId();
	}

	private function getShipping()
	{
		return $this->getQuote()->getShippingAddress()->getShippingAmount();
	}

	private function getInsurance()
	{
		return 0;
	}

	private function getCurrencyCode()
	{
		return $this->getQuote()->getQuoteCurrencyCode();
	}

	/**
	 * Get active or custom quote
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	public function getQuote()
	{
		if (null === $this->_quote)
		{
			$this->_quote = $this->getCheckout()->getQuote();
		}
		return $this->_quote;
	}
}
