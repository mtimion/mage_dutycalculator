<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Dutycalculator_Charge_Model_Sales_Pdf_Grandtotal extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    /**
     * Check if tax amount should be included to grandtotals block
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
		if (!$this->getOrder()->getDcOrderId() || $this->getOrder()->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU || $this->getOrder()->getFailedCalculation())
		{
			$store = $this->getOrder()->getStore();
			$config= Mage::getSingleton('tax/config');
			if (!$config->displaySalesTaxWithGrandTotal($store)) {
				return parent::getTotalsForDisplay();
			}
			$amount = $this->getOrder()->formatPriceTxt($this->getAmount());
			$amountExclTax = $this->getAmount() - $this->getSource()->getTaxAmount();
			$amountExclTax = ($amountExclTax > 0) ? $amountExclTax : 0;
			$amountExclTax = $this->getOrder()->formatPriceTxt($amountExclTax);
			$tax = $this->getOrder()->formatPriceTxt($this->getSource()->getTaxAmount());
			$fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

			$totals = array(array(
								'amount'    => $this->getAmountPrefix().$amountExclTax,
								'label'     => Mage::helper('tax')->__('Grand Total (Excl. Tax)') . ':',
								'font_size' => $fontSize
							));

			if ($config->displaySalesFullSummary($store)) {
				$totals = array_merge($totals, $this->getFullTaxInfo());
			}

			$totals[] = array(
				'amount'    => $this->getAmountPrefix().$tax,
				'label'     => Mage::helper('tax')->__('Tax') . ':',
				'font_size' => $fontSize
			);
			$totals[] = array(
				'amount'    => $this->getAmountPrefix().$amount,
				'label'     => Mage::helper('tax')->__('Grand Total (Incl. Tax)') . ':',
				'font_size' => $fontSize
			);
			return $totals;
		}
		else
		{
			$store = $this->getOrder()->getStore();
			$config= Mage::getSingleton('tax/config');
			$amount = $this->getOrder()->formatPriceTxt($this->getAmount());
			$amountExclTax = $this->getAmount() - ($this->getSource()->getTaxAmount() + $this->getSource()->getImportDutyTax());
			$amountExclTax = ($amountExclTax > 0) ? $amountExclTax : 0;
			$amountExclTax = $this->getOrder()->formatPriceTxt($amountExclTax);
			$tax = $this->getOrder()->formatPriceTxt($this->getSource()->getTaxAmount() + $this->getSource()->getSalesTax());
			$duty = $this->getOrder()->formatPriceTxt($this->getSource()->getImportDuty());
			$fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

			$totals = array(array(
								'amount'    => $this->getAmountPrefix().$amountExclTax,
								'label'     => Mage::helper('tax')->__('Grand Total (Excl. Tax)') . ':',
								'font_size' => $fontSize
							));

//			if ($config->displaySalesFullSummary($store)) {
//				$totals = array_merge($totals, $this->getFullTaxInfo());
//			}

			$totals[] = array(
				'amount'    => $this->getAmountPrefix().$tax,
				'label'     => Mage::helper('tax')->__('Sales Tax') . ':',
				'font_size' => $fontSize
			);

			$title = ($this->getOrder()->getDeliveryDutyType() == Dutycalculator_Charge_Helper_Data::DC_DELIVERY_TYPE_DDU) ? ($this->getOrder()->getFailedCalculation() ? 'Any import duty & taxes are paid upon delivery and are not included in the final price' : 'Estimated import duty & taxes (Not included in grand total, paid upon delivery)') : 'Import duty & additional taxes';
			$totals[] = array(
				'amount'    => $this->getAmountPrefix().$duty,
				'label'     => Mage::helper('dccharge')->__($title) . ':',
				'font_size' => $fontSize
			);
			$totals[] = array(
				'amount'    => $this->getAmountPrefix().$amount,
				'label'     => Mage::helper('tax')->__('Grand Total (Incl. Tax)') . ':',
				'font_size' => $fontSize
			);
			return $totals;
		}
    }
}
