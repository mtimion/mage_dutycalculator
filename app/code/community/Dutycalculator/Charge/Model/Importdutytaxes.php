<?php

class Dutycalculator_Charge_Model_Importdutytaxes extends Varien_Object
{
	public static function getRatesDetails($shippingAddress, $categoriesIds, $descriptions, $references)
	{
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$params = array();
		$params['to'] = $shippingAddress->getCountryId();
		if ($shippingAddress->getRegionCode())
		{
			$params['province'] = $shippingAddress->getRegionCode();
		}
		$params['classify_by'] = 'cat desc';
		$params['detailed_result'] = 1;
		$params['cat'] = array();
		$params['desc'] = array();
		foreach ($categoriesIds as $idx => $id)
		{
			$params['cat'][$idx] = $id;
		}
		foreach ($descriptions as $idx => $description)
		{
			$params['desc'][$idx] = $description;
		}
		$rawXml = $helper->sendRequest('get-hscode', $params);
		try
		{
			$result = array();
			if (stripos($rawXml, '<?xml') === false)
			{
				throw new Exception($rawXml);
			}
			$answer = new SimpleXMLElement($rawXml);
			$rates = $answer->xpath('classification');
			foreach ($rates as $idx => $rate)
			{
				$result[$references[$idx]] = array('duty_rate' => (float)$rate->duty, 'sales_tax_rate' => (float)current($rate->xpath('sales-tax')));
			}
			return $result;
		}
		catch (Exception $ex)
		{
			$result = array();
			return $result;
		}
	}

	public static function getAmount($quote)
	{
		/* @var $quote Mage_Sales_Model_Quote */
		$quoteItems = $quote->getAllVisibleItems();

		$shippingAddress = $quote->getShippingAddress();

		if (!$shippingAddress->getCountryId())
		{
			return 0;
		}
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$params = array();
//		$params['from'] = Mage::getStoreConfig('general/country/default');
		$params['from'] = (Mage::getStoreConfig('shipping/origin/country_id') ? Mage::getStoreConfig('shipping/origin/country_id') : Mage::getStoreConfig('general/country/default'));
		$params['to'] = $shippingAddress->getCountryId();
		if ($shippingAddress->getRegionCode())
		{
			$params['province'] = $shippingAddress->getRegionCode();
		}
		$params['insurance'] = 0;

		if ($quote instanceof Mage_Sales_Model_Order || $quote instanceof Mage_Sales_Model_Order_Invoice)
		{
			$params['shipping'] = (float)$quote->getShippingAmount();
			$params['currency'] = $quote->getOrderCurrencyCode();
			$params['output_currency'] = $quote->getOrderCurrencyCode();
		}
		else
		{
			$params['shipping'] = (float)$shippingAddress->getShippingAmount();
			$params['currency'] = $quote->getQuoteCurrencyCode();
			$params['output_currency'] = $quote->getQuoteCurrencyCode();
		}

		$params['commercial_importer'] = false;
		$params['imported_wt'] = 0;
		$params['imported_value'] = 0;
		$params['detailed_result'] = 1;
		$params['save_failed'] = 1;
		$params['classify_by'] = 'cat desc';
		$params['cat'] = array();
		$params['qty'] = array();
		$params['value'] = array();
		$params['desc'] = array();
		$params['reference'] = array();
		$params['wt'] = array();
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
			if ($quoteItem instanceof Mage_Sales_Model_Order_Invoice_Item || $quoteItem instanceof Mage_Sales_Model_Order_Shipment_Item)
			{
				$product = Mage::getModel('catalog/product')->load($quoteItem->getOrderItem()->getProductId());
				$quoteItemId = $quoteItem->getOrderItem()->getQuoteItemId();
			}
			elseif ($quoteItem instanceof Mage_Sales_Model_Order_Item)
			{
				$product = Mage::getModel('catalog/product')->load($quoteItem->getProductId());
				$quoteItemId = $quoteItem->getQuoteItemId();
			}
			else
			{
				$product = $quoteItem->getProduct();
				$product->load($product->getId());
				$quoteItemId = $quoteItem->getId();
			}
			if ($quoteItem instanceof Mage_Sales_Model_Order_Item)
			{
				$qty = $quoteItem->getQtyOrdered();
			}
			else
			{
				$qty = $quoteItem->getQty();
			}
			if ($product->isVirtual() || $qty <= 0)
			{
				continue;
			}
			$params['reference'][$idx] = $quoteItemId;

			$params['qty'][$idx] = (float)$qty;

			if ($product->getDcProductId())
			{
				$params['cat'][$idx] = $product->getDcProductId();
			}
			else
			{
				$params['cat'][$idx] = '';
			}

			if ($product->getCountryOfManufacture())
			{
				$params['origin'][$idx] = $product->getCountryOfManufacture();
			}

			if ($quoteItem instanceof Mage_Sales_Model_Order_Invoice_Item || $quoteItem instanceof Mage_Sales_Model_Order_Shipment_Item)
			{
				$params['value'][$idx] = (float)$quoteItem->getPrice();
			}
			elseif ($quoteItem instanceof Mage_Sales_Model_Order_Item)
			{
				$params['value'][$idx] = (float)$quoteItem->getPrice();
			}
			else
			{
				$params['value'][$idx] = (float)$quoteItem->getCalculationPrice();
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

			if ($product->getWeight())
			{
				$weigthUnit = Mage::getStoreConfig('dc_charge_extension/dccharge/weight-unit');
				$weight = (Mage::getStoreConfig('dc_charge_extension/dccharge/allow-override-products-weight') ? Mage::getStoreConfig('dc_charge_extension/dccharge/overridden-products-weight') : $product->getWeight());
				if ($weigthUnit == 'lb')
				{
					$itemWeightInKG = round($weight * 0.45359237, 2);
				}
				else
				{
					$itemWeightInKG = $weight;
				}
				$params['wt'][$idx] = (float)$itemWeightInKG;
			}
			else
			{
				$params['wt'][$idx] = 0;
			}
			$idx++;
		}
		$params['use_defaults'] = 1;
		$rawXml = $helper->sendRequest('calculation', $params);
		try
		{
			if (stripos($rawXml, '<?xml') === false)
			{
				throw new Exception($rawXml);
			}
			$answer = new SimpleXMLElement($rawXml);
			$answerAttributes = $answer->attributes();
			$dcOrderId = (int)$answerAttributes['id'];
			$totals = current($answer->xpath('total-charges'));
			$items = $answer->xpath('item');

			$result = array();
			$result['failed_calculation'] = (int)$answerAttributes['failed-calculation'];
			$result['dc_order_id'] = $dcOrderId;
			$result['total'] = (float)$totals->total->amount;
			$result['duty'] = (float)$totals->duty->amount;
			$additionalTaxes = $totals->xpath('additional-import-taxes');
			if ($additionalTaxes)
			{
				$additionalTaxes = current($additionalTaxes);
				foreach ($additionalTaxes->tax as $additionalTax)
				{
					$result['duty'] += (float)$additionalTax->amount;
				}
			}
			$result['sales_tax'] = (float)current($totals->xpath('sales-tax'))->amount;
			$rates = array();
//			if (!$result['failed_calculation'])
//			{
//				$rates = self::getRatesDetails($shippingAddress, $params['cat'], $params['desc'], $params['reference']);
//			}
			$result['items'] = array();
			$result['aggregated_items'] = array();
			foreach ($items as $item)
			{
				$attributes = $item->attributes();
				$references = explode(',', (string)$attributes->reference);
				if (count($references) > 1)
				{
					$total = (float)$item->total->amount;
					$duty = (float)$item->duty->amount;
					$additionalTaxes = $item->xpath('additional-import-taxes');
					if ($additionalTaxes)
					{
						$additionalTaxes = current($additionalTaxes);
						foreach ($additionalTaxes->tax as $additionalTax)
						{
							$duty += (float)$additionalTax->amount;
						}
					}
					$salesTax = (float)current($item->xpath('sales-tax'))->amount;
					$result['aggregated_items'][(string)$attributes->reference] = array('items' => $references, 'aggregated_total' => $total, 'aggregated_duty' => $duty, 'aggregated_sales_tax' => $salesTax);
//					foreach ($references as $reference)
//					{
//						if (isset($rates[$reference]))
//						{
//							$result['aggregated_items'][(string)$attributes->reference]['duty_rate'] = $rates[$reference]['duty_rate'];
//							$result['aggregated_items'][(string)$attributes->reference]['sales_tax_rate'] = $rates[$reference]['sales_tax_rate'];
//						}
//					}

				}
				else
				{
					$total = (float)$item->total->amount;
					$duty = (float)$item->duty->amount;
					$additionalTaxes = $item->xpath('additional-import-taxes');
					if ($additionalTaxes)
					{
						$additionalTaxes = current($additionalTaxes);
						foreach ($additionalTaxes->tax as $additionalTax)
						{
							$duty += (float)$additionalTax->amount;
						}
					}
					$salesTax = (float)current($item->xpath('sales-tax'))->amount;
					$result['items'][(string)$attributes->reference] = array('total' => $total, 'duty' => $duty, 'sales_tax' => $salesTax);
//					if (isset($rates[(string)$attributes->reference]))
//					{
//						$result['items'][(string)$attributes->reference]['duty_rate'] = $rates[(string)$attributes->reference]['duty_rate'];
//						$result['items'][(string)$attributes->reference]['sales_tax_rate'] = $rates[(string)$attributes->reference]['sales_tax_rate'];
//					}
				}
			}
			return $result; //(float)$totals->total->amount;
		}
		catch (Exception $ex)
		{
			$result = array();
			$result['failed_calculation'] = 1;
			$result['dc_order_id'] = 0;
			$result['total'] = 0;
			$result['duty'] = 0;
			$result['sales_tax'] = 0;
			$result['items'] = array();
			$result['aggregated_items'] = array();
			return $result; //(float)$totals->total->amount;
		}
	}

	public static function storeCreditMemoCalculation(Mage_Sales_Model_Order_Creditmemo $creditMemo)
	{
		$params = array();
		$params['calculation_id'] = $creditMemo->getDcOrderId();
		$params['order_id'] = $creditMemo->getOrder()->getIncrementId();
		$params['order_type'] = 'credit_note';
		$params['credit_note_id'] = $creditMemo->getIncrementId();
		$params['assign_addresses'] = 1;
		$params['output_currency'] = $creditMemo->getOrderCurrencyCode();
		$params['seller_first_name'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-first-name');
		$params['seller_last_name'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-last-name');
		$params['seller_country'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-country');
		$params['seller_address_line_1'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-address-line');
		$params['seller_city'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-city');
		$params['seller_zip'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-postcode');
		$params['seller_phone'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-phone');
		$shippingAddress = $creditMemo->getOrder()->getShippingAddress();
		$billingAddress = $creditMemo->getOrder()->getBillingAddress();
		$params['shipto_first_name'] = $shippingAddress->getFirstname();
		$params['shipto_last_name'] = $shippingAddress->getLastname();
		$params['shipto_address_line_1'] = $shippingAddress->getStreet(-1);
		$params['shipto_city'] = $shippingAddress->getCity();
		$params['shipto_zip'] = $shippingAddress->getPostcode();
		$params['shipto_country'] = $shippingAddress->getCountryId();
		$params['shipto_phone'] = $shippingAddress->getTelephone();
		$params['soldto_first_name'] = $billingAddress->getFirstname();
		$params['soldto_last_name'] = $billingAddress->getLastname();
		$params['soldto_address_line_1'] = $billingAddress->getStreet(-1);
		$params['soldto_city'] = $billingAddress->getCity();
		$params['soldto_zip'] = $billingAddress->getPostcode();
		$params['soldto_country'] = $billingAddress->getCountryId();
		$params['soldto_phone'] = $billingAddress->getTelephone();
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$rawXml = $helper->sendRequest('store_calculation', $params);
		try
		{
			if (stripos($rawXml, '<?xml') === false)
			{
				throw new Exception($rawXml);
			}
			new SimpleXMLElement($rawXml);
			return true;
		}
		catch (Exception $ex)
		{
			return false;
		}
	}

	public static function storeShipmentCalculation(Mage_Sales_Model_Order_Shipment $shipment)
	{
		$params = array();
		$params['calculation_id'] = $shipment->getDcOrderId();
		$params['order_id'] = $shipment->getOrder()->getIncrementId();
		$params['order_type'] = 'order';
		$params['shipment_id'] = $shipment->getIncrementId();
		$params['assign_addresses'] = 1;
		$params['output_currency'] = $shipment->getOrder()->getOrderCurrencyCode();
		$params['seller_first_name'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-first-name');
		$params['seller_last_name'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-last-name');
		$params['seller_country'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-country');
		$params['seller_address_line_1'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-address-line');
		$params['seller_city'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-city');
		$params['seller_zip'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-postcode');
		$params['seller_phone'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-phone');
		$shippingAddress = $shipment->getShippingAddress();
		$billingAddress = $shipment->getBillingAddress();
		$params['shipto_first_name'] = $shippingAddress->getFirstname();
		$params['shipto_last_name'] = $shippingAddress->getLastname();
		$params['shipto_address_line_1'] = $shippingAddress->getStreet(-1);
		$params['shipto_city'] = $shippingAddress->getCity();
		$params['shipto_zip'] = $shippingAddress->getPostcode();
		$params['shipto_country'] = $shippingAddress->getCountryId();
		$params['shipto_phone'] = $shippingAddress->getTelephone();
		$params['soldto_first_name'] = $billingAddress->getFirstname();
		$params['soldto_last_name'] = $billingAddress->getLastname();
		$params['soldto_address_line_1'] = $billingAddress->getStreet(-1);
		$params['soldto_city'] = $billingAddress->getCity();
		$params['soldto_zip'] = $billingAddress->getPostcode();
		$params['soldto_country'] = $billingAddress->getCountryId();
		$params['soldto_phone'] = $billingAddress->getTelephone();
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');

		$rawXml = $helper->sendRequest('store_calculation', $params);
		try
		{
			if (stripos($rawXml, '<?xml') === false)
			{
				throw new Exception($rawXml);
			}
			new SimpleXMLElement($rawXml);
			return true;
		}
		catch (Exception $ex)
		{
			return false;
		}
	}

	public static function invoiceCalculation(Mage_Sales_Model_Order_Invoice $invoice)
	{
		$params = array();
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$invoiceItems = $invoice->getAllItems();

		$params['calculation_id'] = $invoice->getOrder()->getDcOrderId();
		$orderShippingAmount = (float)$invoice->getOrder()->getShippingAmount();
		if ($orderShippingAmount)
		{
			foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice)
			{
				if ($previusInvoice->getShippingAmount() && $previusInvoice->getDcOrderId() != 0 && !$previusInvoice->isCanceled())
				{
					$orderShippingAmount = 0;
				}
			}
		}
		else
		{
			$orderShippingAmount = 0;
		}
		$params['shipping'] = $orderShippingAmount;
		$params['output_currency'] = $invoice->getOrderCurrencyCode();
		$params['cat'] = array();
		$params['qty'] = array();
		$params['reference'] = array();
		$idx = 0;
		$itemsToSend = 0;
		foreach ($invoiceItems as $invoiceItem)
		{
			$orderItem = $invoiceItem->getOrderItem();
			$product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
			$qty = $invoiceItem->getQty();
			if ($orderItem->getParentItemId() || !$orderItem->getQuoteItemId() || $product->isVirtual() || $qty <= 0)
			{
				continue;
			}
			$itemsToSend++;
			/* @var $invoiceItem Mage_Sales_Model_Order_Invoice_Item */
			/* @var $product Mage_Catalog_Model_Product */
			$params['qty'][$idx] = (float)$qty;
			$params['reference'][$idx] = $orderItem->getQuoteItemId();
			if ($product->getDcProductId())
			{
				$params['cat'][$idx] = $product->getDcProductId();
			}
			else
			{
				$params['cat'][$idx] = '';
			}
			$idx++;
		}
		if ($itemsToSend > 0)
		{
			$rawXml = $helper->sendRequest('invoice_calculation', $params);
			try
			{
				if (stripos($rawXml, '<?xml') === false)
				{
					throw new Exception($rawXml);
				}
				$answer = new SimpleXMLElement($rawXml);
				$answerAttributes = $answer->attributes();
				$dcOrderId = (int)$answerAttributes['id'];
				$totals = current($answer->xpath('total-charges'));
				$items = $answer->xpath('item');
				$result = array();
				$result['dc_order_id'] = $dcOrderId;
				$result['total'] = (float)$totals->total->amount;
				$result['duty'] = (float)$totals->duty->amount;
				$additionalTaxes = $totals->xpath('additional-import-taxes');
				if ($additionalTaxes)
				{
					$additionalTaxes = current($additionalTaxes);
					foreach ($additionalTaxes->tax as $additionalTax)
					{
						$result['duty'] += (float)$additionalTax->amount;
					}
				}
				$result['sales_tax'] = (float)current($totals->xpath('sales-tax'))->amount;
				$result['items'] = array();
				$result['aggregated_items'] = array();
				foreach ($items as $item)
				{
					$attributes = $item->attributes();
					$references = explode(',', (string)$attributes->reference);
					if (count($references) > 1)
					{
						$total = (float)$item->total->amount;
						$duty = (float)$item->duty->amount;
						$additionalTaxes = $item->xpath('additional-import-taxes');
						if ($additionalTaxes)
						{
							$additionalTaxes = current($additionalTaxes);
							foreach ($additionalTaxes->tax as $additionalTax)
							{
								$duty += (float)$additionalTax->amount;
							}
						}
						$salesTax = (float)current($item->xpath('sales-tax'))->amount;
						$result['aggregated_items'][(string)$attributes->reference] = array('items' => $references, 'aggregated_total' => $total, 'aggregated_duty' => $duty, 'aggregated_sales_tax' => $salesTax);
					}
					else
					{
						$total = (float)$item->total->amount;
						$duty = (float)$item->duty->amount;
						$additionalTaxes = $item->xpath('additional-import-taxes');
						if ($additionalTaxes)
						{
							$additionalTaxes = current($additionalTaxes);
							foreach ($additionalTaxes->tax as $additionalTax)
							{
								$duty += (float)$additionalTax->amount;
							}
						}
						$salesTax = (float)current($item->xpath('sales-tax'))->amount;
						$result['items'][(string)$attributes->reference] = array('total' => $total, 'duty' => $duty, 'sales_tax' => $salesTax);
					}
				}
				return $result;
			}
			catch (Exception $ex)
			{
				$result = array();
				$result['failed_calculation'] = 1;
				$result['dc_order_id'] = 0;
				$result['total'] = 0;
				$result['duty'] = 0;
				$result['sales_tax'] = 0;
				$result['items'] = array();
				$result['aggregated_items'] = array();
				return $result;
			}
		}
		else
		{
			return false;
		}
	}

	public static function creditMemoCalculation(Mage_Sales_Model_Order_Creditmemo $creditMemo)
	{
		$params = array();
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$creditMemoItems = $creditMemo->getAllItems();

		$params['calculation_id'] = $creditMemo->getOrder()->getDcOrderId();
		$params['shipping'] = (float)$creditMemo->getShippingAmount();
		$params['output_currency'] = $creditMemo->getOrderCurrencyCode();
		$params['cat'] = array();
		$params['qty'] = array();
		$params['reference'] = array();
		$idx = 0;
		$itemsToSend = 0;
		foreach ($creditMemoItems as $creditMemoItem)
		{
			$orderItem = $creditMemoItem->getOrderItem();
			$product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
			$qty = $creditMemoItem->getQty();
			if ($orderItem->getParentItemId() || !$orderItem->getQuoteItemId() || $product->isVirtual() || $qty <= 0)
			{
				continue;
			}
			$itemsToSend++;
			/* @var $creditMemoItem Mage_Sales_Model_Order_Creditmemo_Item */
			/* @var $product Mage_Catalog_Model_Product */
			$params['qty'][$idx] = (float)$qty;
			$params['reference'][$idx] = $orderItem->getQuoteItemId();
			if ($product->getDcProductId())
			{
				$params['cat'][$idx] = $product->getDcProductId();
			}
			else
			{
				$params['cat'][$idx] = '';
			}
			$idx++;
		}
		if ($itemsToSend > 0)
		{
			$rawXml = $helper->sendRequest('credit_note_calculation', $params);
			try
			{
				if (stripos($rawXml, '<?xml') === false)
				{
					throw new Exception($rawXml);
				}
				$answer = new SimpleXMLElement($rawXml);
				$answerAttributes = $answer->attributes();
				$dcOrderId = (int)$answerAttributes['id'];
				$totals = current($answer->xpath('total-charges'));
				$items = $answer->xpath('item');
				$result = array();
				$result['dc_order_id'] = $dcOrderId;
				$result['total'] = (float)$totals->total->amount;
				$result['duty'] = (float)$totals->duty->amount;
				$additionalTaxes = $totals->xpath('additional-import-taxes');
				if ($additionalTaxes)
				{
					$additionalTaxes = current($additionalTaxes);
					foreach ($additionalTaxes->tax as $additionalTax)
					{
						$result['duty'] += (float)$additionalTax->amount;
					}
				}
				$result['sales_tax'] = (float)current($totals->xpath('sales-tax'))->amount;
				$result['items'] = array();
				$result['aggregated_items'] = array();
				foreach ($items as $item)
				{
					$attributes = $item->attributes();
					$references = explode(',', (string)$attributes->reference);
					if (count($references) > 1)
					{
						$total = (float)$item->total->amount;
						$duty = (float)$item->duty->amount;
						$additionalTaxes = $item->xpath('additional-import-taxes');
						if ($additionalTaxes)
						{
							$additionalTaxes = current($additionalTaxes);
							foreach ($additionalTaxes->tax as $additionalTax)
							{
								$duty += (float)$additionalTax->amount;
							}
						}
						$salesTax = (float)current($item->xpath('sales-tax'))->amount;
						$result['aggregated_items'][(string)$attributes->reference] = array('items' => $references, 'aggregated_total' => $total, 'aggregated_duty' => $duty, 'aggregated_sales_tax' => $salesTax);
					}
					else
					{
						$total = (float)$item->total->amount;
						$duty = (float)$item->duty->amount;
						$additionalTaxes = $item->xpath('additional-import-taxes');
						if ($additionalTaxes)
						{
							$additionalTaxes = current($additionalTaxes);
							foreach ($additionalTaxes->tax as $additionalTax)
							{
								$duty += (float)$additionalTax->amount;
							}
						}
						$salesTax = (float)current($item->xpath('sales-tax'))->amount;
						$result['items'][(string)$attributes->reference] = array('total' => $total, 'duty' => $duty, 'sales_tax' => $salesTax);
					}
				}
				return $result; //(float)$totals->total->amount;
			}
			catch (Exception $ex)
			{
				$result = array();
				$result['failed_calculation'] = 1;
				$result['dc_order_id'] = 0;
				$result['total'] = 0;
				$result['duty'] = 0;
				$result['sales_tax'] = 0;
				$result['items'] = array();
				$result['aggregated_items'] = array();
				return $result;
			}
		}
		else
		{
			return false;
		}
	}

	public static function shipmentCalculation(Mage_Sales_Model_Order_Shipment $shipment)
	{
		$params = array();
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$shipmentItems = $shipment->getAllItems();

		$params['calculation_id'] = $shipment->getOrder()->getDcOrderId();
		if ($shipment->getOrder()->getShipmentsCollection()->getSize())
		{
			$params['shipping'] = 0;
		}
		else
		{
			$params['shipping'] = (float)$shipment->getOrder()->getShippingAmount();
		}
		$params['output_currency'] = $shipment->getOrder()->getOrderCurrencyCode();
		$params['cat'] = array();
		$params['qty'] = array();
		$params['reference'] = array();
		$idx = 0;
		$itemsToSend = 0;
		foreach ($shipmentItems as $shipmentItem)
		{
			$orderItem = $shipmentItem->getOrderItem();
			$product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
			$qty = $shipmentItem->getQty();
			if ($orderItem->getParentItemId() || !$orderItem->getQuoteItemId() || $product->isVirtual() || $qty <= 0)
			{
				continue;
			}
			$itemsToSend++;
			/* @var $shipmentItem Mage_Sales_Model_Order_Shipment_Item */
			/* @var $product Mage_Catalog_Model_Product */

			$params['qty'][$idx] = (float)$qty;
			$params['reference'][$idx] = $orderItem->getQuoteItemId();
			if ($product->getDcProductId())
			{
				$params['cat'][$idx] = $product->getDcProductId();
			}
			else
			{
				$params['cat'][$idx] = '';
			}
			$idx++;
		}
		if ($itemsToSend > 0)
		{
			$rawXml = $helper->sendRequest('shipment_calculation', $params);
			try
			{
				if (stripos($rawXml, '<?xml') === false)
				{
					throw new Exception($rawXml);
				}
				$answer = new SimpleXMLElement($rawXml);
				$answerAttributes = $answer->attributes();
				$dcOrderId = (int)$answerAttributes['id'];
				$totals = current($answer->xpath('total-charges'));
				$items = $answer->xpath('item');
				$result = array();
				$result['dc_order_id'] = $dcOrderId;
				$result['total'] = (float)$totals->total->amount;
				$result['duty'] = (float)$totals->duty->amount;
				$additionalTaxes = $totals->xpath('additional-import-taxes');
				if ($additionalTaxes)
				{
					$additionalTaxes = current($additionalTaxes);
					foreach ($additionalTaxes->tax as $additionalTax)
					{
						$result['duty'] += (float)$additionalTax->amount;
					}
				}
				$result['sales_tax'] = (float)current($totals->xpath('sales-tax'))->amount;
				$result['items'] = array();
				$result['aggregated_items'] = array();
				foreach ($items as $item)
				{
					$attributes = $item->attributes();
					$references = explode(',', (string)$attributes->reference);
					if (count($references) > 1)
					{
						$total = (float)$item->total->amount;
						$duty = (float)$item->duty->amount;
						$additionalTaxes = $item->xpath('additional-import-taxes');
						if ($additionalTaxes)
						{
							$additionalTaxes = current($additionalTaxes);
							foreach ($additionalTaxes->tax as $additionalTax)
							{
								$duty += (float)$additionalTax->amount;
							}
						}
						$salesTax = (float)current($item->xpath('sales-tax'))->amount;
						$result['aggregated_items'][(string)$attributes->reference] = array('items' => $references, 'aggregated_total' => $total, 'aggregated_duty' => $duty, 'aggregated_sales_tax' => $salesTax);
					}
					else
					{
						$total = (float)$item->total->amount;
						$duty = (float)$item->duty->amount;
						$additionalTaxes = $item->xpath('additional-import-taxes');
						if ($additionalTaxes)
						{
							$additionalTaxes = current($additionalTaxes);
							foreach ($additionalTaxes->tax as $additionalTax)
							{
								$duty += (float)$additionalTax->amount;
							}
						}
						$salesTax = (float)current($item->xpath('sales-tax'))->amount;
						$result['items'][(string)$attributes->reference] = array('total' => $total, 'duty' => $duty, 'sales_tax' => $salesTax);
					}
				}
				return $result; //(float)$totals->total->amount;
			}
			catch (Exception $ex)
			{
				$result = array();
				$result['failed_calculation'] = 1;
				$result['dc_order_id'] = 0;
				$result['total'] = 0;
				$result['duty'] = 0;
				$result['sales_tax'] = 0;
				$result['items'] = array();
				$result['aggregated_items'] = array();
				return $result;
			}
		}
		else
		{
			return false;
		}
	}

	public static function canApply(Mage_Sales_Model_Quote_Address $address)
	{
		$helper = Mage::helper('dccharge');
//		$deliveryType = Mage::getStoreConfig('dc_charge_extension/dccharge/delivery-type');
		$countryFrom = (Mage::getStoreConfig('shipping/origin/country_id') ? Mage::getStoreConfig('shipping/origin/country_id') : Mage::getStoreConfig('general/country/default'));
		if ($countryFrom && $address->getCountryId() != $countryFrom && $helper->canUseForCountry($address->getCountryId()) && $address->getShippingMethod() && $helper->canUseForShippingMethod($address->getShippingMethod()))
		{
			return true;
		}
		return false;
	}

	public static function generateDocuments(Mage_Sales_Model_Order_Shipment $shipment)
	{
		$result = array();
		if ($shipment->getDcOrderId() && Mage::getStoreConfig('dc_charge_extension/dccharge/create-documents'))
		{
			$helper = Mage::helper('dccharge');
			$params = array();
			$params['calculation_id'] = $shipment->getDcOrderId();
			$params['output_currency'] = $shipment->getOrder()->getOrderCurrencyCode();
			$params['seller_first_name'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-first-name');
			$params['seller_last_name'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-last-name');
			$params['seller_country'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-country');
			$params['seller_address_line_1'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-address-line');
			$params['seller_city'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-city');
			$params['seller_zip'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-postcode');
			$params['seller_phone'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-phone');
			$params['shipment_invoice_no'] = $shipment->getIncrementId();
			$params['shipment_date'] = $shipment->getCreatedAt();
			$params['shipment_number_parcels'] = 1;
			$params['shipment_total_actual_weight'] = 0;
			foreach ($shipment->getAllItems() as $item)
			{
				$orderItem = $item->getOrderItem();
				$product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
				if ($orderItem->getParentItemId() || !$orderItem->getQuoteItemId() || $product->isVirtual())
				{
					continue;
				}
				$itemWeightInKG = 0;
				if ($item->getWeight())
				{
					$weigthUnit = Mage::getStoreConfig('dc_charge_extension/dccharge/weight-unit');
					$weight = (Mage::getStoreConfig('dc_charge_extension/dccharge/allow-override-products-weight') ? Mage::getStoreConfig('dc_charge_extension/dccharge/overridden-products-weight') : $item->getWeight());
					if ($weigthUnit == 'lb')
					{
						$itemWeightInKG = round($weight * 0.45359237, 2);
					}
					else
					{
						$itemWeightInKG = $weight;
					}
				}
				$params['shipment_total_actual_weight'] += $itemWeightInKG * $item->getQty();
			}
			$params['shipment_currency_sale'] = $shipment->getOrder()->getOrderCurrencyCode();
			if ($shipment->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDP)
			{
				$params['shipment_incoterms'] = 'DDP';
			}
			elseif ($shipment->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU)
			{
				$params['shipment_incoterms'] = 'DAP';
			}
			else
			{
				$params['shipment_incoterms'] = '';
			}
			$shippingAddress = $shipment->getShippingAddress();
			$billingAddress = $shipment->getBillingAddress();
			$params['shipto_first_name'] = $shippingAddress->getFirstname();
			$params['shipto_last_name'] = $shippingAddress->getLastname();
			$params['shipto_address_line_1'] = $shippingAddress->getStreet(-1);
			$params['shipto_city'] = $shippingAddress->getCity();
			$params['shipto_zip'] = $shippingAddress->getPostcode();
			$params['shipto_country'] = $shippingAddress->getCountryId();
			$params['shipto_phone'] = $shippingAddress->getTelephone();
			$params['soldto_first_name'] = $billingAddress->getFirstname();
			$params['soldto_last_name'] = $billingAddress->getLastname();
			$params['soldto_address_line_1'] = $billingAddress->getStreet(-1);
			$params['soldto_city'] = $billingAddress->getCity();
			$params['soldto_zip'] = $billingAddress->getPostcode();
			$params['soldto_country'] = $billingAddress->getCountryId();
			$params['soldto_phone'] = $billingAddress->getTelephone();
			$params['print_first_name'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-first-name');
			$params['print_last_name'] = Mage::getStoreConfig('dc_charge_extension/dccharge/seller-last-name');
			$params['print_date'] = date('Y-m-d');
			$rawXml = $helper->sendRequest('documents', $params);
			try
			{
				if (stripos($rawXml, '<?xml') === false)
				{
					throw new Exception($rawXml);
				}
				$answer = new SimpleXMLElement($rawXml);
				$commercialInvoice = current($answer->xpath('commercial-invoice'));
				$packingList = current($answer->xpath('packing-list'));
				$result['commercial_invoice_url'] = (string)$commercialInvoice->url;
				$result['packing_list_url'] = (string)$packingList->url;
			}
			catch (Exception $ex)
			{
				Mage::logException($ex);
			}
		}
		return $result;
	}
}