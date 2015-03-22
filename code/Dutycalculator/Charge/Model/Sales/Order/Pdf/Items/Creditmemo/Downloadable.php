<?php
/**
 * Downloadable.php created by a.voytik.
 * Date: 17/04/2012 06:30
 */

class Dutycalculator_Charge_Model_Sales_Order_Pdf_Items_Creditmemo_Downloadable extends Mage_Downloadable_Model_Sales_Order_Pdf_Items_Creditmemo
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

			$leftBound  =  35;
			$rightBound = 565;

			$x = $leftBound;
			// draw Product name
			$lines[0] = array(array(
								  'text' => Mage::helper('core/string')->str_split($item->getName(), 60, true, true),
								  'feed' => $x,
							  ));

			$x += 200;
			// draw SKU
			$lines[0][] = array(
				'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 25),
				'feed'  => $x
			);

			$x += 80;//100
			// draw Total (ex)
			$lines[0][] = array(
				'text'  => $order->formatPriceTxt($item->getRowTotal()),
				'feed'  => $x,
				'font'  => 'bold',
				'align' => 'right',
				'width' => 50,
			);

			$x += 50;
			// draw Discount
			$lines[0][] = array(
				'text'  => $order->formatPriceTxt(-$item->getDiscountAmount()),
				'feed'  => $x,
				'font'  => 'bold',
				'align' => 'right',
				'width' => 50,
			);

			$x += 50;
			// draw QTY
			$lines[0][] = array(
				'text'  => $item->getQty()*1,
				'feed'  => $x,
				'font'  => 'bold',
				'align' => 'center',
				'width' => 30,
			);

			// draw Tax
			$lines[0][] = array(
				'text'  => $order->formatPriceTxt($item->getTaxAmount() + $item->getSalesTax()),
				'feed'  => $x,
				'font'  => 'bold',
				'align' => 'right',
				'width' => 35,//45
			);

			$x += 40; //45

			// draw Tax
			$lines[0][] = array(
				'text'  => $order->formatPriceTxt($item->getImportDuty()),
				'feed'  => $x,
				'font'  => 'bold',
				'align' => 'right',
				'width' => 40,//45
			);

			$x += 45; //45
			
			// draw Subtotal
			$lines[0][] = array(
				'text'  => $order->formatPriceTxt($item->getRowTotal() + $item->getTaxAmount() + $item->getImportDutyTax() - $item->getDiscountAmount()),
				'feed'  => $rightBound,
				'font'  => 'bold',
				'align' => 'right'
			);

			// draw options
			$options = $this->getItemOptions();
			if ($options) {
				foreach ($options as $option) {
					// draw options label
					$lines[][] = array(
						'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
						'font' => 'italic',
						'feed' => $leftBound
					);

					// draw options value
					$_printValue = isset($option['print_value'])
						? $option['print_value']
						: strip_tags($option['value']);
					$lines[][] = array(
						'text' => Mage::helper('core/string')->str_split($_printValue, 50, true, true),
						'feed' => $leftBound + 5
					);
				}
			}

			// downloadable Items
			$_purchasedItems = $this->getLinks()->getPurchasedItems();

			// draw Links title
			$lines[][] = array(
				'text' => Mage::helper('core/string')->str_split($this->getLinksTitle(), 70, true, true),
				'font' => 'italic',
				'feed' => 35
			);

			// draw Links
			foreach ($_purchasedItems as $_link) {
				$lines[][] = array(
					'text' => Mage::helper('core/string')->str_split($_link->getLinkTitle(), 50, true, true),
					'feed' => 40
				);
			}

			$lineBlock = array(
				'lines'  => $lines,
				'height' => 10
			);

			$page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
			$this->setPage($page);
		}
	}

	/**
	 * Draw item line
	 *
	 */
	public function draw1()
	{
		$order  = $this->getOrder();
		$item   = $this->getItem();
		$pdf    = $this->getPdf();
		$page   = $this->getPage();
		$lines  = array();

		// draw Product name
		$lines[0] = array(array(
							  'text' => Mage::helper('core/string')->str_split($item->getName(), 64, true, true),
							  'feed' => 35,
						  ));

		// draw SKU
		$lines[0][] = array(
			'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 25),
			'feed'  => 255
		);

		/// draw QTY
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
			'text'  => $order->formatPriceTxt($item->getTaxAmount() + $item->getImportDutyTax),
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

		// downloadable Items
		$_purchasedItems = $this->getLinks()->getPurchasedItems();

		// draw Links title
		$lines[][] = array(
			'text' => Mage::helper('core/string')->str_split($this->getLinksTitle(), 70, true, true),
			'font' => 'italic',
			'feed' => 35
		);

		// draw Links
		foreach ($_purchasedItems as $_link) {
			$lines[][] = array(
				'text' => Mage::helper('core/string')->str_split($_link->getLinkTitle(), 50, true, true),
				'feed' => 40
			);
		}

		$lineBlock = array(
			'lines'  => $lines,
			'height' => 10
		);

		$page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
		$this->setPage($page);
	}
}