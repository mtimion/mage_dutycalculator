<?php

class Dutycalculator_Charge_Helper_Tax extends Mage_Tax_Helper_Data{
	public function getPrice($product, $price, $includingTax = null, $shippingAddress = null, $billingAddress = null,
							 $ctc = null, $store = null, $priceIncludesTax = null
	) {
		$price=parent::getPrice($product, $price, $includingTax, $shippingAddress, $billingAddress, $ctc, $store, $priceIncludesTax);

		if(Mage::app()->getFrontController()->getRequest()->getModuleName()=='catalog' && Mage::getStoreConfig('dc_charge_extension/dccharge/consolidated-price')){
			$product->load($product->getId());
			$dcHelper=Mage::helper('dccharge');
			$params=array(
				'classify_by'=>'cat desc',
				'cat[0]'=>$product->getDcProductId(),
				'desc[0]'=>$product->getName(),
				'value[0]'=>$price,
				'qty[0]'=>1,
				'from'=>(Mage::getStoreConfig('shipping/origin/country_id') ? Mage::getStoreConfig('shipping/origin/country_id') : Mage::getStoreConfig('general/country/default')),
				'to'=>Mage::getStoreConfig('dc_charge_extension/dccharge/consolidated-price-country'),
				'currency'=>Mage::app()->getStore()->getBaseCurrency()->getCode(),
				'output_currency'=>Mage::app()->getStore()->getBaseCurrency()->getCode(),
				'insurance'=>0,
				'shipping'=>0,
				'is_consolidated_request'=>1
			);
			if ($product->getCountryOfManufacture())
			{
				$params['origin[0]'] = $product->getCountryOfManufacture();
			}
			$params['use_defaults'] = 1;

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
				$params['wt[0]'] = (float)$itemWeightInKG;
			}

			$rawXml=$dcHelper->sendRequest('calculation',$params);
			if (stripos($rawXml, '<?xml') === false)
			{
				return $price;
			}
			$answer = new SimpleXMLElement($rawXml);
			$totals = current($answer->xpath('total-charges'));
			$totalCharges = (float)$totals->total->amount;

			$price+=$totalCharges;
		}
		return $price;
	}
}