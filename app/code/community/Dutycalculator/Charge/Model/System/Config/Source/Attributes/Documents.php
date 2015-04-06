<?php
/**
 * Allowspecificshippings.php created by a.voytik.
 * Date: 12/04/2012 05:40
 */

class Dutycalculator_Charge_Model_System_Config_Source_Attributes_Documents
{
	protected $_options;

	public function toOptionArray()
	{
		if (!isset($this->_options))
		{
			$entityType = Mage::getSingleton('eav/entity_type')->loadByCode('catalog_product');
			/* @var $attributesCollection Mage_Eav_Model_Resource_Entity_Attribute_Collection */
			$attributesCollection = Mage::getModel('eav/entity_attribute')->getResourceCollection();
			$attributesCollection->setEntityTypeFilter($entityType);
			$attributesCollection->addFieldToFilter('frontend_label', array('notnull' => 1));
			$attributesCollection->addFieldToFilter('frontend_input', array('nin' => array('date', 'media_image', 'gallery')));
			$attributesCollection->addFieldToFilter('frontend_input', array('notnull' => 1));
//			$attributesCollection->addFieldToFilter('attribute_code', array('nin' => array('sku', 'name', 'description', 'dc_product_id')));
			$attributesCollection->setOrder('frontend_label', 'ASC');
			$attributesCollection->load();
			$options = array();
			foreach ($attributesCollection as $attribute)
			{
				$options[] = array('label' => $attribute->getFrontendLabel(), 'value' => $attribute->getAttributeCode());
			}
			$this->_options = $options;
		}
		return $this->_options;
	}
}
