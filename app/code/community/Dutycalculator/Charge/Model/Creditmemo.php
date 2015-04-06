<?php

class Dutycalculator_Charge_Model_Creditmemo extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	protected $_code = 'import_duty_tax';

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditMemo)
    {
		$currencyFrom = Mage::getModel('directory/currency')->load($creditMemo->getOrderCurrencyCode());
		$currencyTo = $creditMemo->getStore()->getBaseCurrency();
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$creditMemo->setImportDutyTax(0);
		$creditMemo->setBaseImportDutyTax(0);
		$creditMemo->setImportDuty(0);
		$creditMemo->setBaseImportDuty(0);
		$creditMemo->setSalesTax(0);
		$creditMemo->setBaseSalesTax(0);
		$creditMemo->setDeliveryDutyType($creditMemo->getOrder()->getDeliveryDutyType());
		$creditMemo->setFailedCalculation($creditMemo->getOrder()->getFailedCalculation());
		$creditMemo->setDcOrderId(0);
		foreach ($creditMemo->getAllItems() as $creditMemoItem)
		{
			$creditMemoItem->setImportDutyTax(0);
			$creditMemoItem->setBaseImportDutyTax(0);
			$creditMemoItem->setImportDuty(0);
			$creditMemoItem->setBaseImportDuty(0);
			$creditMemoItem->setSalesTax(0);
			$creditMemoItem->setBaseSalesTax(0);
		}
		if ($creditMemo->getOrder()->getDcOrderId())
		{
			$result = Dutycalculator_Charge_Model_Importdutytaxes::creditMemoCalculation($creditMemo);
			if ($result)
			{
				$amountToRefund = $result['total'];
				$baseAmountToRefund = $helper->convertPrice($currencyFrom, $currencyTo, $result['total']);
				$creditMemo->setDeliveryDutyType($creditMemo->getOrder()->getDeliveryDutyType());
				$creditMemo->setFailedCalculation($creditMemo->getOrder()->getFailedCalculation());

				$creditMemo->setImportDutyTax($result['total']);
				$creditMemo->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $result['total']));
				$creditMemo->setImportDuty($result['duty']);
				$creditMemo->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $result['duty']));
				$creditMemo->setSalesTax($result['sales_tax']);
				$creditMemo->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $result['sales_tax']));

				$creditMemo->setDcOrderId($result['dc_order_id']);
				if ($creditMemo->getOrder()->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP)
				{
					$creditMemo->setGrandTotal($creditMemo->getGrandTotal() + $amountToRefund);
					$creditMemo->setBaseGrandTotal($creditMemo->getBaseGrandTotal() + $baseAmountToRefund);
					$aggregatedItemsValues = array();
					foreach ($creditMemo->getAllItems() as $creditMemoItem)
					{
						if ($creditMemoItem->getOrderItem()->getParentItemId())
						{
							continue;
						}
						$id = $creditMemoItem->getOrderItem()->getQuoteItemId();
						if (isset($result['items'][$id]))
						{
							$creditMemoItem->setImportDutyTax($result['items'][$id]['total']);
							$creditMemoItem->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $result['items'][$id]['total']));
							$creditMemoItem->setImportDuty($result['items'][$id]['duty']);
							$creditMemoItem->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $result['items'][$id]['duty']));
							$creditMemoItem->setSalesTax($result['items'][$id]['sales_tax']);
							$creditMemoItem->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $result['items'][$id]['sales_tax']));
						}
						else
						{
							foreach ($result['aggregated_items'] as $key => $_items)
							{
								if (in_array($id, $_items['items']))
								{
									$aggregatedItemsValues[$key][$id] = $creditMemoItem->getRowTotal();
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
					foreach ($creditMemo->getAllItems() as $creditMemoItem)
					{
						if ($creditMemoItem->getOrderItem()->getParentItemId())
						{
							continue;
						}
						$id = $creditMemoItem->getOrderItem()->getQuoteItemId();
						if (isset($taxes[$id]))
						{
							$creditMemoItem->setImportDutyTax($totals[$id]);
							$creditMemoItem->setBaseImportDutyTax($helper->convertPrice($currencyFrom, $currencyTo, $totals[$id]));
							$creditMemoItem->setImportDuty($totalDuty[$id]);
							$creditMemoItem->setBaseImportDuty($helper->convertPrice($currencyFrom, $currencyTo, $totalDuty[$id]));
							$creditMemoItem->setSalesTax($totalSalesTaxes[$id]);
							$creditMemoItem->setBaseSalesTax($helper->convertPrice($currencyFrom, $currencyTo, $totalSalesTaxes[$id]));
						}
						if ($creditMemoItem->getQty() == 0)
						{
							$creditMemoItem->setImportDutyTax(0);
							$creditMemoItem->setBaseImportDutyTax(0);
							$creditMemoItem->setImportDuty(0);
							$creditMemoItem->setBaseImportDuty(0);
							$creditMemoItem->setSalesTax(0);
							$creditMemoItem->setBaseSalesTax(0);
						}
					}
				}
			}
		}
        return $this;
    }
}
