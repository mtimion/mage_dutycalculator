<?php

class Dutycalculator_Charge_Block_Cart_Shipping_Service_Fee extends Mage_Checkout_Block_Cart_Abstract
{
	public function getDeliveryDutyOptions()
	{
		return array(Mage::helper('dccharge')->getDeliveryDutyPaidType()=>$this->__('Pay import duty & tax now'),Mage::helper('dccharge')->getDeliveryDutyUnpaidType()=>$this->__('Pay import duty & tax upon delivery'));
	}

	public function isOptional()
	{
		$helper = Mage::helper('dccharge');
//		$deliveryType = Mage::getStoreConfig('dc_charge_extension/dccharge/delivery-type');
		/* @var $quote Mage_Sales_Model_Quote */
		$quote = Mage::getSingleton('checkout/cart')->getQuote();
		$address = $quote->getShippingAddress();
		if ($address->getId())
		{
			$countryFrom = (Mage::getStoreConfig('shipping/origin/country_id') ? Mage::getStoreConfig('shipping/origin/country_id') : Mage::getStoreConfig('general/country/default'));
			if ($countryFrom &&
				$address->getCountryId() != $countryFrom &&
				$helper->canUseForCountry($address->getCountryId()) &&
				$address->getShippingMethod() &&
				$helper->canUseForShippingMethod($address->getShippingMethod()) &&
				!$quote->getFailedCalculation())
			{
				return Mage::getStoreConfig('dc_charge_extension/dccharge/delivery-type') == Mage::helper('dccharge')->getDeliveryDutyOptionalType();
			}
		}
		return false;
	}

	private function getCountryFromCode()
	{
		return (Mage::getStoreConfig('shipping/origin/country_id') ? Mage::getStoreConfig('shipping/origin/country_id') : Mage::getStoreConfig('general/country/default'));
	}

	private function getCountryToCode($quote)
	{
		return $quote->getShippingAddress()->getCountryId();
	}

	public function getCurrentDeliveryOption()
	{
		$quote = Mage::getSingleton('checkout/cart')->getQuote();
		if ($quote->getFailedCalculation())
		{
			//when it is a failed calculation - DDU
			return 	Mage::helper('dccharge')->getDeliveryDutyUnpaidType();
		}

		$userChoice = $quote->getDeliveryDutyUserChoice();
		//print '>'.$userChoice;
		if (!$userChoice)
		{
			//When user hasn't selected anything - we'll use the one that is set to quote
			return $quote->getDeliveryDutyType();
		}
		else
		{
			return $userChoice;
		}
	}
}
