<?php
/**
 * Product tags admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Dutycalculator_Charge_Adminhtml_DocumentsController extends Mage_Adminhtml_Controller_Action
{
	public function massPrintAction()
    {
		$orderIds = $this->getRequest()->getPost('order_ids');
		$flag = false;
		if (!empty($orderIds)) {
			foreach ($orderIds as $orderId) {
				$shipments = Mage::getResourceModel('sales/order_shipment_collection')
					->setOrderFilter($orderId);
				$shipments->getSelect()->where('commercial_invoice_url IS NOT NULL')->where('packing_list_url IS NOT NULL');
				$shipments->load();
				if ($shipments->getSize()) {
					$flag = true;
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
			}
			if ($flag) {
				return $this->_prepareDownloadResponse(
					'PackingListsAndCommercialInvoices-'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
					'application/pdf'
				);
			} else {
				$this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
				$this->_redirect('adminhtml/sales_order/');
			}
		}
		$this->_redirect('adminhtml/sales_order/');
    }
}