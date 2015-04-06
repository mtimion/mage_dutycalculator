<?php
/**
 * Observer.php created by a.voytik.
 * Date: 12/03/2012 07:35
 */
class Dutycalculator_Charge_Model_Adminhtml_Observer
{
	public function prepareForm($observer)
	{
		$form = $observer->getForm();
		if ($dcCategory = $form->getElement('dc_product_id'))
		{
			/* @var $form Varien_Data_Form */
			$dcCategory->setRenderer(Mage::app()->getLayout()->createBlock('dccharge/adminhtml_category'));
		}
		return $this;
	}

	public function invoiceSaveAfter(Varien_Event_Observer $observer)
	{
		/* @var $invoice Mage_Sales_Model_Order_Invoice */
		$invoice = $observer->getEvent()->getInvoice();
		if ($invoice->getBaseImportDutyTax())
		{
			$order = $invoice->getOrder();
			$order->setImportDutyTaxInvoiced($order->getImportDutyTaxInvoiced() + $invoice->getImportDutyTax());
			$order->setBaseImportDutyTaxInvoiced($order->getBaseImportDutyTaxInvoiced() + $invoice->getBaseImportDutyTax());
			foreach ($invoice->getAllItems() as $item)
			{
				/* @var $item Mage_Sales_Model_Order_Invoice_Item */
				$orderItem = $item->getOrderItem();
				$orderItem->setImportDutyTaxInvoiced($orderItem->getImportDutyTaxInvoiced() + $item->getImportDutyTax());
				$orderItem->setBaseImportDutyTaxInvoiced($orderItem->getBaseImportDutyTaxInvoiced() + $item->getBaseImportDutyTax());
			}
		}
		return $this;
	}

	public function creditmemoSaveAfter(Varien_Event_Observer $observer)
	{
		/* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
		$creditmemo = $observer->getEvent()->getCreditmemo();
		if ($creditmemo->getImportDutyTax())
		{
			$order = $creditmemo->getOrder();
			$order->setImportDutyTaxRefunded($order->getImportDutyTaxRefunded() + $creditmemo->getImportDutyTax());
			$order->setBaseImportDutyTaxRefunded($order->getBaseImportDutyTaxRefunded() + $creditmemo->getBaseImportDutyTax());
			foreach ($creditmemo->getAllItems() as $item)
			{
				/* @var $item Mage_Sales_Model_Order_Creditmemo_Item */
				$orderItem = $item->getOrderItem();
				$orderItem->setImportDutyTaxRefunded($orderItem->getImportDutyTaxRefunded() + $item->getImportDutyTax());
				$orderItem->setBaseImportDutyTaxRefunded($orderItem->getBaseImportDutyTaxRefunded() + $item->getBaseImportDutyTax());
			}
			Dutycalculator_Charge_Model_Importdutytaxes::storeCreditMemoCalculation($creditmemo);
		}
		return $this;
	}

	public function generateDutyCalculatorDocuments(Varien_Event_Observer $observer)
	{
		/* @var $shipment Mage_Sales_Model_Order_Shipment */
		$shipment = $observer->getShipment();
		if ($shipment->getDcOrderId())
		{
			$result = Dutycalculator_Charge_Model_Importdutytaxes::generateDocuments($shipment);
			if ($result)
			{
				$shipment->setCommercialInvoiceUrl($result['commercial_invoice_url']);
				$shipment->setPackingListUrl($result['packing_list_url']);
				$shipment->getResource()->save($shipment);
			}
			Dutycalculator_Charge_Model_Importdutytaxes::storeShipmentCalculation($shipment);
		}
		return $this;
	}

	public function shipmentSaveBefore(Varien_Event_Observer $observer)
	{
		/* @var $shipment Mage_Sales_Model_Order_Shipment */
		$shipment = $observer->getShipment();
		if ($shipment->getOrder()->getDcOrderId() && !$shipment->getDcOrderId())
		{
			$result = Dutycalculator_Charge_Model_Importdutytaxes::shipmentCalculation($shipment);
			if ($result)
			{
				$shipment->setDcOrderId($result['dc_order_id']);
			}
		}
		$shipment->setDeliveryDutyType($shipment->getOrder()->getDeliveryDutyType());
		$shipment->setFailedCalculation($shipment->getOrder()->getFailedCalculation());
		return $this;
	}

	public function addPrintingActions($event)
	{
		$block = $event->getBlock();
		if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract && $block->getRequest()->getControllerName() == 'sales_order')
		{
			$block->addItem('dccharge_mass_print', array(
											  'label' => Mage::helper('dccharge')->__('Print Commercial Invoices & Packing Lists'),
											  'url' => Mage::app()->getStore()->getUrl('dccharge/adminhtml_documents/massPrint'),
										 ));
		}
	}

	public function salesOrderAddressSaveAfter($event)
	{
		/* @var $salesOrderAddress Mage_Sales_Model_Order_Address */
		$salesOrderAddress = $event->getAddress();
		try
		{
			$shipmentsCollection = $salesOrderAddress->getOrder()->getShipmentsCollection();
			if ($shipmentsCollection->getSize())
			{
				foreach ($shipmentsCollection as $shipment)
				{
					$result = Dutycalculator_Charge_Model_Importdutytaxes::generateDocuments($shipment);
					if ($result)
					{
						$shipment->setCommercialInvoiceUrl($result['commercial_invoice_url']);
						$shipment->setPackingListUrl($result['packing_list_url']);
					}
				}
				$shipmentsCollection->save();
			}
		}
		catch (Exception $ex)
		{

		}
		return $this;
	}
}