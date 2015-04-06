<?php

class Dutycalculator_Charge_Model_Invoice extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
	protected $_code = 'import_duty_tax';

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
		$currencyFrom = Mage::getModel('directory/currency')->load($invoice->getOrderCurrencyCode());
		$currencyTo = $invoice->getStore()->getBaseCurrency();
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$invoice->setImportDutyTax(0);
		$invoice->setBaseImportDutyTax(0);
		$invoice->setImportDuty(0);
		$invoice->setBaseImportDuty(0);
		$invoice->setSalesTax(0);
		$invoice->setBaseSalesTax(0);
		$invoice->setDeliveryDutyType($invoice->getOrder()->getDeliveryDutyType());
		$invoice->setFailedCalculation($invoice->getOrder()->getFailedCalculation());
		$invoice->setDcOrderId(0);
		foreach ($invoice->getAllItems() as $invoiceItem)
		{
			$invoiceItem->setImportDutyTax(0);
			$invoiceItem->setBaseImportDutyTax(0);
			$invoiceItem->setImportDuty(0);
			$invoiceItem->setBaseImportDuty(0);
			$invoiceItem->setSalesTax(0);
			$invoiceItem->setBaseSalesTax(0);
		}
		if ($invoice->getOrder()->getDcOrderId())
		{
			$result = Dutycalculator_Charge_Model_Importdutytaxes::invoiceCalculation($invoice);
			if ($result)
			{
				$amountToInvoice = $result['total'];
				$baseAmountToInvoice = $helper->convertPrice($currencyFrom, $currencyTo, $result['total']);
				$invoice->setImportDutyTax($result['total']);
				$invoice->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $result['total']));
				$invoice->setImportDuty($result['duty']);
				$invoice->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $result['duty']));
				$invoice->setSalesTax($result['sales_tax']);
				$invoice->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $result['sales_tax']));

				$invoice->setDeliveryDutyType($invoice->getOrder()->getDeliveryDutyType());
				$invoice->setFailedCalculation($invoice->getOrder()->getFailedCalculation());
				$invoice->setDcOrderId($result['dc_order_id']);
				if ($invoice->getOrder()->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP)
				{
					$invoice->setGrandTotal($invoice->getGrandTotal() + $amountToInvoice);
					$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseAmountToInvoice);
					$aggregatedItemsValues = array();
					foreach ($invoice->getAllItems() as $invoiceItem)
					{
						if ($invoiceItem->getOrderItem()->getParentItemId())
						{
							continue;
						}
						$id = $invoiceItem->getOrderItem()->getQuoteItemId();
						if (isset($result['items'][$id]))
						{
							$invoiceItem->setImportDutyTax($result['items'][$id]['total']);
							$invoiceItem->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $result['items'][$id]['total']));
							$invoiceItem->setImportDuty($result['items'][$id]['duty']);
							$invoiceItem->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $result['items'][$id]['duty']));
							$invoiceItem->setSalesTax($result['items'][$id]['sales_tax']);
							$invoiceItem->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $result['items'][$id]['sales_tax']));
						}
						else
						{
							foreach ($result['aggregated_items'] as $key => $_items)
							{
								if (in_array($id, $_items['items']))
								{
									$aggregatedItemsValues[$key][$id] = $invoiceItem->getRowTotal();
								}
							}
						}
					}
					$totals = array();
					$totalDuty = array();
					$totalSalesTaxes = array();
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
						}
					}
					foreach ($invoice->getAllItems() as $invoiceItem)
					{
						if ($invoiceItem->getOrderItem()->getParentItemId())
						{
							continue;
						}
						$id = $invoiceItem->getOrderItem()->getQuoteItemId();
						if (isset($taxes[$id]))
						{
							$invoiceItem->setImportDutyTax($totals[$id]);
							$invoiceItem->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $totals[$id]));
							$invoiceItem->setImportDuty($totalDuty[$id]);
							$invoiceItem->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $totalDuty[$id]));
							$invoiceItem->setSalesTax($totalSalesTaxes[$id]);
							$invoiceItem->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $totalSalesTaxes[$id]));
						}
						if ($invoiceItem->getQty() == 0)
						{
							$invoiceItem->setImportDutyTax(0);
							$invoiceItem->setBaseImportDutyTax(0);
							$invoiceItem->setImportDuty(0);
							$invoiceItem->setBaseImportDuty(0);
							$invoiceItem->setSalesTax(0);
							$invoiceItem->setBaseSalesTax(0);
						}
					}
				}
			}
		}
        return $this;
    }
}
