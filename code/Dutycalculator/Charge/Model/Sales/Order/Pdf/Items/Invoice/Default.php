<?php
/**
 * Default.php created by a.voytik.
 * Date: 17/04/2012 06:29
 */

class Dutycalculator_Charge_Model_Sales_Order_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default
{
	/**
	 * Draw item line
	 *
	 */
	public function draw()
	{
		$order  = $this->getOrder();
		if (!$order->getDcOrderId() || $order->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU || $order->getFailedCalculation())
		{
			parent::draw();
		}
		else
		{
			$item   = $this->getItem();
			$pdf    = $this->getPdf();
			$page   = $this->getPage();
			$lines  = array();

			// draw Product name
			$lines[0] = array(array(
								  'text' => Mage::helper('core/string')->str_split($item->getName(), 60, true, true),
								  'feed' => 35,
							  ));

			// draw SKU
			$lines[0][] = array(
				'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 25),
				'feed'  => 255
			);

			// draw QTY
			$lines[0][] = array(
				'text'  => $item->getQty()*1,
				'feed'  => 415 //435
			);

			// draw Price
			$lines[0][] = array(
				'text'  => $order->formatPriceTxt($item->getPrice()),
				'feed'  => 375,//395
				'font'  => 'bold',
				'align' => 'right'
			);

			// draw Tax
			$lines[0][] = array(
				'text'  => $order->formatPriceTxt($item->getTaxAmount() + $item->getSalesTax()),
				'feed'  => 480, //495
				'font'  => 'bold',
				'align' => 'right'
			);

			$lines[0][] = array(
				'text'  => $order->formatPriceTxt($item->getImportDuty()),
				'feed'  => 520, //495
				'font'  => 'bold',
				'align' => 'right'
			);

			// draw Subtotal
			$lines[0][] = array(
				'text'  => $order->formatPriceTxt($item->getRowTotal()),
				'feed'  => 565,
				'font'  => 'bold',
				'align' => 'right'
			);

			// custom options
			$options = $this->getItemOptions();
			if ($options) {
				foreach ($options as $option) {
					// draw options label
					$lines[][] = array(
						'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
						'font' => 'italic',
						'feed' => 35
					);

					if ($option['value']) {
						$_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
						$values = explode(', ', $_printValue);
						foreach ($values as $value) {
							$lines[][] = array(
								'text' => Mage::helper('core/string')->str_split($value, 50, true, true),
								'feed' => 40
							);
						}
					}
				}
			}

			$lineBlock = array(
				'lines'  => $lines,
				'height' => 10
			);

			$page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
			$this->setPage($page);
		}
	}
}