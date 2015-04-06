<?php
/**
 * Shipment.php created by a.voytik.
 * Date: 07/05/2012 11:24
 */

class Dutycalculator_Charge_Model_Sales_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Shipment
{
	public function getPdf($shipments = array())
	{
		$pdf = parent::getPdf($shipments);
		try
		{
			foreach ($shipments as $shipment)
			{
				if (!isset($pdf)){
					$pdf = new Zend_Pdf();
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $shipment->getCommercialInvoiceUrl());
					curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1000);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$contents = curl_exec($curl);
					curl_close($curl);
					$_pdf = new Zend_Pdf($contents);
					foreach ($_pdf->pages as $page)
					{
						$pdf->pages[] = clone $page;
					}
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $shipment->getPackingListUrl());
					curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1000);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$contents = curl_exec($curl);
					curl_close($curl);
					$_pdf = new Zend_Pdf($contents);
					foreach ($_pdf->pages as $page)
					{
						$pdf->pages[] = clone $page;
					}
				} else {
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $shipment->getCommercialInvoiceUrl());
					curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1000);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$commercialInvoice = curl_exec($curl);
					curl_close($curl);
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $shipment->getPackingListUrl());
					curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1000);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					$packingList = curl_exec($curl);
					curl_close($curl);
					$_pdf = new Zend_Pdf($commercialInvoice);
					foreach ($_pdf->pages as $page)
					{
						$pdf->pages[] = clone $page;
					}
					$_pdf = new Zend_Pdf($packingList);
					foreach ($_pdf->pages as $page)
					{
						$pdf->pages[] = clone $page;
					}
				}
			}
		}
		catch (Exception $ex)
		{
			Mage::logException($ex);
		}
		return $pdf;
	}
}