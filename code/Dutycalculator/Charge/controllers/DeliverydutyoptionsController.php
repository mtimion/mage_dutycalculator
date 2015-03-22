<?php

class Dutycalculator_Charge_DeliverydutyoptionsController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Get one page checkout model
	 *
	 * @return Mage_Checkout_Model_Type_Onepage
	 */
	public function getOnepage()
	{
		return Mage::getSingleton('checkout/type_onepage');
	}

	/**
	 * Get one page checkout model
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	public function getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}

	public function changeAction()
	{
		$deliveryDutyType=(string) $this->getRequest()->getParam('delivery_duty_type');
		Mage::getSingleton('checkout/cart')->getQuote()->setDeliveryDutyUserChoice($deliveryDutyType)->save();
		$this->_redirect('checkout/cart');
	}

	public function onepageChangeAction()
	{
		$response = array();
		$response['error'] = false;
		try
		{
			$deliveryDutyType = (string)$this->getRequest()->getParam('delivery_duty_type');
			$this->getCheckout()->getQuote()->setDeliveryDutyUserChoice($deliveryDutyType)->collectTotals()->save();
		}
		catch (Exception $e)
		{
			$response['error'] = true;
		}
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
	}

	public function payPalExpressChangeAction()
	{
		$response = array();
		$response['error'] = false;
		try
		{
			$deliveryDutyType = (string)$this->getRequest()->getParam('delivery_duty_type');
			$this->getCheckout()->getQuote()->setDeliveryDutyUserChoice($deliveryDutyType)->collectTotals()->save();
		}
		catch (Exception $e)
		{
			$response['error'] = true;
		}
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
	}

	public function multishippingChangeAction()
	{
		$response = array();
		$response['error'] = false;
		try
		{
			$deliveryDutyType = (string)$this->getRequest()->getParam('delivery_duty_type');
			$this->getCheckout()->getQuote()->setDeliveryDutyUserChoice($deliveryDutyType)->collectTotals()->save();
		}
		catch (Exception $e)
		{
			$response['error'] = true;
		}
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
	}
}