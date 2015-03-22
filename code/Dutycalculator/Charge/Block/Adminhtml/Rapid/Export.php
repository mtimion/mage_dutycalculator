<?php


class Dutycalculator_Charge_Block_Adminhtml_Rapid_Export extends Mage_Adminhtml_Block_Widget_Form
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('export_form');
		$this->setTitle(Mage::helper('dccharge')->__('Block Information'));
	}

	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
										  'id'	 => 'export_form',
										  'action' => $this->getUrl('*/*/export'),
										  'method' => 'post'
									 ));

		$fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('dccharge')->__('Export file for DutyCalculator Rapid Classification tool'),
															 'comment' => 'With DutyCalculator Rapid Classification Tool you are able to classify product catalogs quickly, or make use of DutyCalculator Managed Product Classification Service. Create the required CVS file here and upload this file into DutyCalculator Rapid Classification Tool'));

		$fieldset->addField('only_active', 'checkbox', array('name'	 => 'only_active',
															 'label'	=> Mage::helper('dccharge')->__('Include only enabled products'),
															 'title'	=> Mage::helper('dccharge')->__('Include only enabled products'),
															 'style' => 'margin-right: 5px;',
															 'checked' => true,
															 'value' => 1));

		$fieldset->addField('only_parent', 'checkbox', array('name'	 => 'only_parent',
															 'label'	=> Mage::helper('dccharge')->__('Exclude not visible individually sub products'),
															 'title'	=> Mage::helper('dccharge')->__('Exclude not visible individually sub products'),
															 'style' => 'margin-right: 5px;',
															 'checked' => true,
															 'value' => 1));

		$fieldset->addField('export_option', 'radios', array('name'	 => 'export_option',
															'separator' => '<br/>',
															'label'	=> Mage::helper('dccharge')->__('Export Option'),
															'title'	=> Mage::helper('dccharge')->__('Export Option'),
															'values'  => Mage::helper('dccharge')->getExportOptions(),
															'style' => 'margin-right: 5px;',
													   		'value' => Dutycalculator_Charge_Helper_Data::DC_EXPORT_ALL_PRODUCTS));

		$fieldset->addField('export_file', 'button', array('value'	 => Mage::helper('dccharge')->__('Export File'),
														   'onclick'   => 'exportForm.submit()',
														   'type'	  => 'button',
														   'name'		=> 'export_file',
														   'class' => 'form-button'));

//		$exportButton->setRenderer($this->getLayout()->createBlock('adminhtml/widget_button'));
//		$exportButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array('label'	 => Mage::helper('dccharge')->__('Export File'),
//																								 'onclick'   => 'exportForm.submit()',
//																								 'type'	  => 'button',
//																								 'id'		=> 'export_file'));
//
//		$fieldset->addElement($exportButton);

		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}