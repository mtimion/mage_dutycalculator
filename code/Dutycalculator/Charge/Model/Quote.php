<?php

class Dutycalculator_Charge_Model_Quote extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	protected $_code = 'import_duty_tax';

	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
		$additionalServiceFeeType = Mage::getStoreConfig('dc_charge_extension/dccharge/ddp-fee-type');
		$additionalServiceFeeValue = Mage::getStoreConfig('dc_charge_extension/dccharge/ddp-fee-value');
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$this->_setAddress($address);
		if ($address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
		{
			$quote = $address->getQuote();
			$currencyFrom = Mage::getModel('directory/currency')->load($quote->getQuoteCurrencyCode());
			$currencyTo = $quote->getStore()->getBaseCurrency();
			$items = $quote->getAllVisibleItems();
			if (!count($items))
			{
				return $this; //this makes only address type shipping to come through
			}
			$address->setImportDutyTax(0);
			$address->setBaseImportDutyTax(0);
			$address->setImportDuty(0);
			$address->setBaseImportDuty(0);
			$address->setSalesTax(0);
			$address->setBaseSalesTax(0);

			$quote->setImportDutyTax(0);
			$quote->setBaseImportDutyTax(0);
			$quote->setImportDuty(0);
			$quote->setBaseImportDuty(0);
			$quote->setSalesTax(0);
			$quote->setBaseSalesTax(0);
			$quote->setDeliveryDutyType(Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU);
			if (Mage::getStoreConfig('dc_charge_extension/dccharge/delivery-type') == Mage::helper('dccharge')->getDeliveryDutyOptionalType() && !$quote->getDeliveryDutyUserChoice())
			{
				$quote->setDeliveryDutyUserChoice(Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP);
			}
			$quote->setFailedCalculation(0);
			$quote->setDcOrderId(0);
			foreach ($items as $quoteItem)
			{
				$quoteItem->setImportDutyTax(0);
				$quoteItem->setBaseImportDutyTax(0);
				$quoteItem->setImportDuty(0);
				$quoteItem->setBaseImportDuty(0);
				$quoteItem->setSalesTax(0);
				$quoteItem->setBaseSalesTax(0);
				$quoteItem->setSalesTaxRate(0);
				$quoteItem->setImportDutyRate(0);
			}
			if (Dutycalculator_Charge_Model_Importdutytaxes::canApply($address) && !$quote->getIsVirtual())
			{
				if ($quote->getDeliveryDutyUserChoice() === Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP || Mage::getStoreConfig('dc_charge_extension/dccharge/delivery-type') === Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP)
				{
					if ($additionalServiceFeeType === Dutycalculator_Charge_Helper_Data::DC_DDP_FEE_TYPE_FIXED)
					{
						$baseAdditionalServiceFee = $additionalServiceFeeValue;
					}
					else if ($additionalServiceFeeType === Dutycalculator_Charge_Helper_Data::DC_DDP_FEE_TYPE_PERCENT)
					{
						$baseAdditionalServiceFee = round($address->getBaseSubtotal() * $additionalServiceFeeValue / 100, 2);
					}
					else
					{
						$baseAdditionalServiceFee = 0;
					}
					$additionalServiceFee = Mage::app()->getStore()->convertPrice($baseAdditionalServiceFee);
				}
				else
				{
					$additionalServiceFee = 0;
					$baseAdditionalServiceFee = 0;
				}
				$shippingAmount = $address->getShippingAmount();
				$baseShippingAmount = $address->getBaseShippingAmount();
				$address->setShippingAmount($shippingAmount + $additionalServiceFee);
				$address->setBaseShippingAmount($baseShippingAmount + $baseAdditionalServiceFee);

				$result = Dutycalculator_Charge_Model_Importdutytaxes::getAmount($quote);
				if (is_array($result))
				{
					$address->setImportDutyTax($result['total']);
					$address->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $result['total']));
					$address->setImportDuty($result['duty']);
					$address->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $result['duty']));
					$address->setSalesTax($result['sales_tax']);
					$address->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $result['sales_tax']));

					$quote->setImportDutyTax($result['total']);
					$quote->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $result['total']));
					$quote->setImportDuty($result['duty']);
					$quote->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $result['duty']));
					$quote->setSalesTax($result['sales_tax']);
					$quote->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $result['sales_tax']));

					if ($result['failed_calculation'])
					{
						$quote->setDeliveryDutyType(Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU);
						$address->setShippingAmount($shippingAmount);
						$address->setBaseShippingAmount($baseShippingAmount);
						$additionalServiceFee = 0;
						$baseAdditionalServiceFee = 0;
					}
					else
					{
						//if optional - we also set to DDP
						if($quote->getDeliveryDutyUserChoice())
						{
							$quote->setDeliveryDutyType($quote->getDeliveryDutyUserChoice());
						}
						else
						{
							$quote->setDeliveryDutyType(Mage::getStoreConfig('dc_charge_extension/dccharge/delivery-type') !== Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU ? Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP : Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU);
						}
					}

					if ($quote->getDeliveryDutyType() === Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP)
					{
						$shippingDescriptionPrefix = '';
						if ($address->getShippingDescription())
						{
							$shippingDescriptionPrefix = ', ';
						}
						$address->setShippingDescription($address->getShippingDescription() . $shippingDescriptionPrefix . 'incl. service fee');
					}


					$quote->setDcServiceFee($additionalServiceFee);
					$quote->setBaseDcServiceFee($baseAdditionalServiceFee);

					$quote->setFailedCalculation($result['failed_calculation']);
					$quote->setDcOrderId($result['dc_order_id']);
					if ($quote->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP)
					{
						$aggregatedItemsValues = array();
						foreach ($items as $quoteItem)
						{
							if (isset($result['items'][$quoteItem->getId()]))
							{

								$quoteItem->setImportDutyTax($result['items'][$quoteItem->getId()]['total']);
								$quoteItem->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $result['items'][$quoteItem->getId()]['total']));
								$quoteItem->setImportDuty($result['items'][$quoteItem->getId()]['duty']);
								$quoteItem->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $result['items'][$quoteItem->getId()]['duty']));
								$quoteItem->setSalesTax($result['items'][$quoteItem->getId()]['sales_tax']);
								$quoteItem->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $result['items'][$quoteItem->getId()]['sales_tax']));
//								$quoteItem->setSalesTaxRate($result['items'][$quoteItem->getId()]['sales_tax_rate']);
//								$quoteItem->setImportDutyRate($result['items'][$quoteItem->getId()]['duty_rate']);
							}
							else
							{
								foreach ($result['aggregated_items'] as $key => $_items)
								{
									if (in_array($quoteItem->getId(), $_items['items']))
									{
										$aggregatedItemsValues[$key][$quoteItem->getId()] = $quoteItem->getRowTotal();
									}
								}
							}
						}
						$totals = array();
						$totalDuty = array();
						$totalSalesTaxes = array();
						$dutyRates = array();
						$salesTaxRates = array();
						foreach ($aggregatedItemsValues as $key => $aggregatedItemsValue)
						{
							$aggregatedTotal = $result['aggregated_items'][$key]['aggregated_total'];
							$aggregatedDuty = $result['aggregated_items'][$key]['aggregated_duty'];
							$aggregatedSalesTax = $result['aggregated_items'][$key]['aggregated_sales_tax'];
							$totalAggregatedItemsValue = array_sum($aggregatedItemsValue);
							foreach ($aggregatedItemsValue as $itemId => $value)
							{
								$totals[$itemId] = round($value / $totalAggregatedItemsValue * $aggregatedTotal , 2);
								$totalDuty[$itemId] = round($value / $totalAggregatedItemsValue * $aggregatedDuty , 2);
								$totalSalesTaxes[$itemId] = round($value / $totalAggregatedItemsValue * $aggregatedSalesTax , 2);
//								$dutyRates[$itemId] = $result['aggregated_items'][$key]['duty_rate'];
//								$salesTaxRates[$itemId] = $result['aggregated_items'][$key]['sales_tax_rate'];
							}
						}
						foreach ($items as $quoteItem)
						{
							if (isset($totals[$quoteItem->getId()]))
							{
								$quoteItem->setImportDutyTax($totals[$quoteItem->getId()]);
								$quoteItem->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $totals[$quoteItem->getId()]));
								$quoteItem->setImportDuty($totalDuty[$quoteItem->getId()]);
								$quoteItem->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $totalDuty[$quoteItem->getId()]));
								$quoteItem->setSalesTax($totalSalesTaxes[$quoteItem->getId()]);
								$quoteItem->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $totalSalesTaxes[$quoteItem->getId()]));
//								$quoteItem->setSalesTaxRate($salesTaxRates[$quoteItem->getId()]);
//								$quoteItem->setImportDutyRate($dutyRates[$quoteItem->getId()]);
							}
						}
						$address->setGrandTotal($address->getGrandTotal() + $address->getImportDutyTax() + $additionalServiceFee);
						$address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseImportDutyTax() + $baseAdditionalServiceFee);
					}
				}
			}
		}
	}

	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{
		if ($address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
		{
			if (Dutycalculator_Charge_Model_Importdutytaxes::canApply($address))
			{
				$amt = $address->getImportDutyTax();
				$title = ($address->getQuote()->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU) ? ($address->getQuote()->getFailedCalculation() ? 'Any import duty & taxes are paid upon delivery and are not included in the final price' : 'Estimated import duty & taxes (Not included in grand total, paid upon delivery)') : 'Import duty and taxes';
				$address->addTotal(array(
										'code' => $this->getCode(),
										'title'=> $title,
										'value'=> $amt
								   ));
			}
		}
		return $this;
	}
}
