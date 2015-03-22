<?php
/**
 * Category.php created by a.voytik.
 * Date: 01/06/2012 11:02
 */

class Dutycalculator_Charge_Model_Attribute_Backend_Model_Category extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
	public function beforeSave($product)
	{
		parent::beforeSave($product);
		/* @var $product Mage_Catalog_Model_Product */
//		if ($product->getDcProductId())
//		{
//			/* @var $childProducts Mage_Catalog_Model_Resource_Product_Collection */
//			$childProducts = Mage::getModel('catalog/product')->getResourceCollection();
//			$childProductsIds = array_values(current($product->getTypeInstance(true)->getChildrenIds($product->getId(), false)));
//			$childProducts->addIdFilter($childProductsIds);
//			$childProducts->load();
//			foreach ($childProducts as $child)
//			{
//				$child->load($child->getId());
//				$child->setDcProductId($product->getDcProductId());
//				$child->save();
//			}
//		}
	}
}