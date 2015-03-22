<?php

class Dutycalculator_Charge_Model_Frontend_Observer
{
	public function updatePaypalTotal($evt)
	{
		$cart = $evt->getPaypalCart();
		$salesEntity = $cart->getSalesEntity();
		try
		{
			if (!$salesEntity->getIsVirtual())
			{
				$items = $salesEntity->getAllVisibleItems();
				if (!count($items))
				{
					return $this;
				}
				$result = false;

				if ($salesEntity instanceof Mage_Sales_Model_Order)
				{
					$quote = Mage::getModel('sales/quote')->load($salesEntity->getQuoteId());
					if ($quote->getId())
					{
						$address = $quote->getShippingAddress();
					}
					else
					{
						throw new Exception('No qoute found');
					}
					$currencyFrom = Mage::getModel('directory/currency')->load($salesEntity->getOrderCurrencyCode());
				}
				else
				{
					$address = $salesEntity->getShippingAddress();
					$currencyFrom = Mage::getModel('directory/currency')->load($salesEntity->getQuoteCurrencyCode());
				}
				if (Dutycalculator_Charge_Model_Importdutytaxes::canApply($address))
				{
					$result = Dutycalculator_Charge_Model_Importdutytaxes::getAmount($salesEntity);
				}
				if ($result)
				{
					if ($result['failed_calculation'])
					{
						$salesEntity->setDeliveryDutyType(Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU);
					}
					else
					{
						if($salesEntity->getDeliveryDutyUserChoice())
						{
							$salesEntity->setDeliveryDutyType($salesEntity->getDeliveryDutyUserChoice());
						}
						else
						{
							$salesEntity->setDeliveryDutyType(Mage::getStoreConfig('dc_charge_extension/dccharge/delivery-type')!==Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU?Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP:Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU);
						}
//						$salesEntity->setDeliveryDutyType(Mage::getStoreConfig('dc_charge_extension/dccharge/delivery-type'));
					}
					/* @var $helper Dutycalculator_Charge_Helper_Data */
					$helper = Mage::helper('dccharge');
					$currencyTo = $salesEntity->getStore()->getBaseCurrency();
					$balance = $helper->convertPrice($currencyFrom, $currencyTo, $result['total']);
					if ($salesEntity->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP)
					{
						$cart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_SHIPPING, $balance);
					}
				}
			}
		}
		catch (Exception $ex)
		{
			$salesEntity->setDeliveryDutyType(Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU);
		}
		return $this;
	}

	public function initDCRates($event)
	{
		/* @var $order Mage_Sales_Model_Order */
		$order = $event->getOrder();
		/* @var $quote Mage_Sales_Model_Quote */
		$quote = $event->getQuote();

		if (!$quote->getFailedCalculation() && !$quote->getIsVirtual())
		{
			$quoteItems = $quote->getAllVisibleItems();
			$params = array();
			$params['cat'] = array();
			$params['desc'] = array();
			$params['reference'] = array();
			$idx = 0;
			$additionalAttributes = explode(',', Mage::getStoreConfig('dc_charge_extension/dccharge/additional-attributes-for-documents'));
			/* @var $attributesCollection Mage_Eav_Model_Resource_Entity_Attribute_Collection */
			$attributesCollection = Mage::getModel('eav/entity_attribute')->getResourceCollection();
			$attributesCollection->setCodeFilter($additionalAttributes);
			$attributesCollection->load();
			$additionalAttributes = array();
			foreach ($attributesCollection as $additionalAttribute)
			{
				$additionalAttributes[$additionalAttribute->getAttributeCode()] = $additionalAttribute->getFrontendLabel();
			}
			foreach ($quoteItems as $quoteItem)
			{
				/* @var $quoteItem Mage_Sales_Model_Quote_Item */
				/* @var $product Mage_Catalog_Model_Product */
				$product = $quoteItem->getProduct();
				$product->load($product->getId());
				$quoteItemId = $quoteItem->getId();
				$qty = $quoteItem->getQty();
				if ($product->isVirtual() || $qty <= 0)
				{
					continue;
				}
				$params['reference'][$idx] = $quoteItemId;
				if ($product->getDcProductId())
				{
					$params['cat'][$idx] = $product->getDcProductId();
				}
				else
				{
					$params['cat'][$idx] = '';
				}
				$description = $product->getName();
				if ($additionalAttributes)
				{
					$additionalDescription = '';
					foreach ($additionalAttributes as $attrCode => $attrLabel)
					{
						if ($product->getAttributeText($attrCode))
						{
							$additionalDescription .= $attrLabel . ': ' . $product->getAttributeText($attrCode) . ', ';
						}
						else if ($product->getData($attrCode))
						{
							$additionalDescription .= $attrLabel . ': ' . $product->getData($attrCode) . ', ';
						}
					}
					if (strlen($additionalDescription))
					{
						$description .= ' ' . rtrim($additionalDescription, ', ');
					}
				}
				$params['desc'][$idx] = $description;
				$idx++;
			}
			$rates = Dutycalculator_Charge_Model_Importdutytaxes::getRatesDetails($quote->getShippingAddress(), $params['cat'], $params['desc'], $params['reference']);
			foreach ($quoteItems as $quoteItem)
			{
				if (isset($rates[$quoteItem->getId()]))
				{
					$quoteItem->setSalesTaxRate($rates[$quoteItem->getId()]['sales_tax_rate']);
					$quoteItem->setImportDutyRate($rates[$quoteItem->getId()]['duty_rate']);
				}
			}
			$quote->save();
			$orderItems = $order->getAllVisibleItems();
			foreach ($orderItems as $orderItem)
			{
				/* @var $orderItem Mage_Sales_Model_Order_Item */
				if (isset($rates[$orderItem->getQuoteItemId()]))
				{
					$orderItem->setSalesTaxRate($rates[$orderItem->getQuoteItemId()]['sales_tax_rate']);
					$orderItem->setImportDutyRate($rates[$orderItem->getQuoteItemId()]['duty_rate']);
				}
			}
		}
	}
}