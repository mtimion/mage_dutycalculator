<?php
/**
 * Default.php created by a.voytik.
 * Date: 17/04/2012 06:29
 */

class Dutycalculator_Charge_Model_Sales_Order_Pdf_Items_Creditmemo_Bundle extends Mage_Bundle_Model_Sales_Order_Pdf_Items_Creditmemo
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

			$items = $this->getChilds($item);
			$_prevOptionId = '';
			$drawItems = array();
			$leftBound  =  35;
			$rightBound = 565;

			foreach ($items as $_item) {
				$x      = $leftBound;
				$line   = array();

				$attributes = $this->getSelectionAttributes($_item);
				if (is_array($attributes)) {
					$optionId   = $attributes['option_id'];
				}
				else {
					$optionId = 0;
				}

				if (!isset($drawItems[$optionId])) {
					$drawItems[$optionId] = array(
						'lines'  => array(),
						'height' => 10
					);
				}

				// draw selection attributes
				if ($_item->getOrderItem()->getParentItem()) {
					if ($_prevOptionId != $attributes['option_id']) {
						$line[0] = array(
							'font'  => 'italic',
							'text'  => Mage::helper('core/string')->str_split($attributes['option_label'],60, true, true),
							'feed'  => $x
						);

						$drawItems[$optionId] = array(
							'lines'  => array($line),
							'height' => 10
						);

						$line = array();
						$_prevOptionId = $attributes['option_id'];
					}
				}

				// draw product titles
				if ($_item->getOrderItem()->getParentItem()) {
					$feed = $x + 5;
					$name = $this->getValueHtml($_item);
				} else {
					$feed = $x;
					$name = $_item->getName();
				}

				$line[] = array(
					'text'  => Mage::helper('core/string')->str_split($name, 60, true, true),
					'feed'  => $feed
				);

				$x += 200;//220

				// draw SKUs
				if (!$_item->getOrderItem()->getParentItem()) {
					$text = array();
					foreach (Mage::helper('core/string')->str_split($item->getSku(), 30) as $part) {
						$text[] = $part;
					}
					$line[] = array(
						'text'  => $text,
						'feed'  => $x
					);
				}

				$x += 80;//100

				// draw prices
				if ($this->canShowPriceInfo($_item)) {
					// draw Total(ex)
					$text = $order->formatPriceTxt($_item->getRowTotal());
					$line[] = array(
						'text'  => $text,
						'feed'  => $x,
						'font'  => 'bold',
						'align' => 'right',
						'width' => 50
					);
					$x += 50;

					// draw Discount
					$text = $order->formatPriceTxt(-$_item->getDiscountAmount());
					$line[] = array(
						'text'  => $text,
						'feed'  => $x,
						'font'  => 'bold',
						'align' => 'right',
						'width' => 50
					);
					$x += 50;

					// draw QTY
					$text = $_item->getQty() * 1;
					$line[] = array(
						'text'  => $_item->getQty()*1,
						'feed'  => $x,
						'font'  => 'bold',
						'align' => 'center',
						'width' => 30
					);
					$x += 30;

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

					// draw Total(inc)
					$text = $order->formatPriceTxt($_item->getRowTotal()+$_item->getTaxAmount() + $_item->getImportDutyTax()-$_item->getDiscountAmount());
					$line[] = array(
						'text'  => $text,
						'feed'  => $rightBound,
						'font'  => 'bold',
						'align' => 'right',
					);
				}

				$drawItems[$optionId]['lines'][] = $line;

			}

			// custom options
			$options = $item->getOrderItem()->getProductOptions();
			if ($options) {
				if (isset($options['options'])) {
					foreach ($options['options'] as $option) {
						$lines = array();
						$lines[][] = array(
							'text'  => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
							'font'  => 'italic',
							'feed'  => $leftBound
						);

						if ($option['value']) {
							$text = array();
							$_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
							$values = explode(', ', $_printValue);
							foreach ($values as $value) {
								foreach (Mage::helper('core/string')->str_split($value, 50, true, true) as $_value) {
									$text[] = $_value;
								}
							}

							$lines[][] = array(
								'text'  => $text,
								'feed'  => $leftBound + 5
							);
						}

						$drawItems[] = array(
							'lines'  => $lines,
							'height' => 10
						);
					}
				}
			}

			$page = $pdf->drawLineBlocks($page, $drawItems, array('table_header' => true));
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

		$this->_setFontRegular();
		$items = $this->getChilds($item);

		$_prevOptionId = '';
		$drawItems = array();

		foreach ($items as $_item) {
			$line   = array();

			$attributes = $this->getSelectionAttributes($_item);
			if (is_array($attributes)) {
				$optionId   = $attributes['option_id'];
			}
			else {
				$optionId = 0;
			}

			if (!isset($drawItems[$optionId])) {
				$drawItems[$optionId] = array(
					'lines'  => array(),
					'height' => 10
				);
			}

			if ($_item->getOrderItem()->getParentItem()) {
				if ($_prevOptionId != $attributes['option_id']) {
					$line[0] = array(
						'font'  => 'italic',
						'text'  => Mage::helper('core/string')->str_split($attributes['option_label'], 70, true, true),
						'feed'  => 35
					);

					$drawItems[$optionId] = array(
						'lines'  => array($line),
						'height' => 10
					);

					$line = array();

					$_prevOptionId = $attributes['option_id'];
				}
			}

			/* in case Product name is longer than 80 chars - it is written in a few lines */
			if ($_item->getOrderItem()->getParentItem()) {
				$feed = 40;
				$name = $this->getValueHtml($_item);
			} else {
				$feed = 35;
				$name = $_item->getName();
			}
			$line[] = array(
				'text'  => Mage::helper('core/string')->str_split($name, 55, true, true),
				'feed'  => $feed
			);

			// draw SKUs
			if (!$_item->getOrderItem()->getParentItem()) {
				$text = array();
				foreach (Mage::helper('core/string')->str_split($item->getSku(), 30) as $part) {
					$text[] = $part;
				}
				$line[] = array(
					'text'  => $text,
					'feed'  => 255
				);
			}

			// draw prices
			if ($this->canShowPriceInfo($_item)) {
				$price = $order->formatPriceTxt($_item->getPrice());
				$line[] = array(
					'text'  => $price,
					'feed'  => 375,//395
					'font'  => 'bold',
					'align' => 'right'
				);
				$line[] = array(
					'text'  => $_item->getQty()*1,
					'feed'  => 415, //435
					'font'  => 'bold',
				);

				$tax = $order->formatPriceTxt($_item->getTaxAmount() + $_item->getImportDutyTax);
				$line[] = array(
					'text'  => $tax,
					'feed'  => 520, //495
					'font'  => 'bold',
					'align' => 'right'
				);

				$row_total = $order->formatPriceTxt($_item->getRowTotal());
				$line[] = array(
					'text'  => $row_total,
					'feed'  => 565,
					'font'  => 'bold',
					'align' => 'right'
				);
			}

			$drawItems[$optionId]['lines'][] = $line;
		}

		// custom options
		$options = $item->getOrderItem()->getProductOptions();
		if ($options) {
			if (isset($options['options'])) {
				foreach ($options['options'] as $option) {
					$lines = array();
					$lines[][] = array(
						'text'  => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
						'font'  => 'italic',
						'feed'  => 35
					);

					if ($option['value']) {
						$text = array();
						$_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
						$values = explode(', ', $_printValue);
						foreach ($values as $value) {
							foreach (Mage::helper('core/string')->str_split($value, 50, true, true) as $_value) {
								$text[] = $_value;
							}
						}

						$lines[][] = array(
							'text'  => $text,
							'feed'  => 40
						);
					}

					$drawItems[] = array(
						'lines'  => $lines,
						'height' => 10
					);
				}
			}
		}

		$page = $pdf->drawLineBlocks($page, $drawItems, array('table_header' => true));

		$this->setPage($page);
	}
}