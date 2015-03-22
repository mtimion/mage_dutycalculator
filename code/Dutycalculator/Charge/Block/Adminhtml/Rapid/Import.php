<?php


class Dutycalculator_Charge_Block_Adminhtml_Rapid_Import extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('import_form');
        $this->setTitle(Mage::helper('tag')->__('Block Information'));
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'import_form',
            'action' => $this->getUrl('*/*/import'),
            'method' => 'post',
			'enctype' => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('dccharge')->__('Import file from DutyCalculator Rapid Classification tool')));

		$fieldset->addField('import_file_btn', 'button', array('value'	 => Mage::helper('dccharge')->__('Import File'),
															   'onclick'   => 'importForm.submit()',
															   'type'	  => 'button',
															   'name'		=> 'import_file_btn',
															   'class' => 'form-button', 'style' => 'margin-left: -205px;'
														 ));

        $fieldset->addField('import_file', 'file', array(
            'name' => 'import_file',
            'label' => Mage::helper('dccharge')->__('Import File'),
            'title' => Mage::helper('dccharge')->__('Import File'),
            'required' => true
        ));



//		$importButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array('label'	 => Mage::helper('dccharge')->__('Import File'),
//																								 'onclick'   => 'importForm.submit()',
//																								 'type'	  => 'button',
//																								 'id'		=> 'import_file_btn'));
//
//		$fieldset->addElement($importButton);

        $form->setUseContainer(true);

        $this->setForm($form);
        return parent::_prepareForm();
    }
}