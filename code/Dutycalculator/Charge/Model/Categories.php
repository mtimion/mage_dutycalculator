<?php
/**
 * Categories.php created by a.voytik.
 * Date: 15/03/2012 05:44
 */
class Dutycalculator_Charge_Model_Categories extends Mage_Core_Model_Abstract
{
	private $_categories;
	private $_suggestedCategories;

	public function getCategories()
	{
		/* @var $helper Dutycalculator_Charge_Helper_Data */
		$helper = Mage::helper('dccharge');
		$description = '';
		$product = Mage::registry('current_product');
		if ($product->getName() && !$product->getDcProductId())
		{
			$description = $product->getName();
		}
		$rawXML = $helper->sendRequest('dc-id-classification', array('product_desc' => $description));
		$categories = array();
		$suggestedCategories = array();
		if (strlen($rawXML) != 0)
		{
			try
			{
				if (stripos($rawXML, '<?xml') === false)
				{
					throw new Exception($rawXML);
				}
				$xml = new SimpleXMLElement($rawXML);
                $idx = 0;
				$xmlSuggestedCategories = $xml->xpath('suggested-categories');
				if ($xmlSuggestedCategories)
				{
					$xmlSuggestedCategories = current($xmlSuggestedCategories);
					$xmlCategories = current($xml->xpath('all-categories'));
				}
				else
				{
					$xmlCategories = $xml->xpath('category');
				}
				if (count($xmlSuggestedCategories))
				{
					$suggestedCategories['items'] = array();
					foreach ($xmlSuggestedCategories as $category)
					{
						/* @var $category SimpleXMLElement*/
						$categoryAttributes = $category->attributes();
						$subCategories = array();
						foreach ($category->subcategory as $subCategory)
						{
							$subCategoryAttributes = $subCategory->attributes();
							$items = array();
							foreach ($subCategory->item as $item)
							{
								$itemAttributes = $item->attributes();
								$items[] = array('id' => (string)$itemAttributes['id']);
							}
							$hash = md5((string)$subCategoryAttributes['name'] . (string)$categoryAttributes['name']);
							$subCategories[] =  array('id' => $hash, 'items' => $items);
						}
						$hash = md5((string)$categoryAttributes['name']);
						$suggestedCategories['items'][] = array('id' => $hash, 'items' => $subCategories);
					}
				}

				foreach ($xmlCategories as $category)
				{
					/* @var $category SimpleXMLElement*/
					$categoryAttributes = $category->attributes();
					$subCategories = array();
					foreach ($category->subcategory as $subCategory)
					{
						$subCategoryAttributes = $subCategory->attributes();
						$items = array();
						foreach ($subCategory->item as $item)
						{
							$itemAttributes = $item->attributes();
							$items[(string)$itemAttributes['id']] = array('value' => (string)$itemAttributes['id'], 'label' => (string)$item);
						}
						$hash = md5((string)$subCategoryAttributes['name'] . (string)$categoryAttributes['name']);
						$subCategories[$hash] = array('value' => $hash, 'label' => (string)$subCategoryAttributes['name'], 'items' => $items);
					}
					$hash = md5((string)$categoryAttributes['name']);
					$categories[$hash] = array('value' => $hash, 'label' => (string)$categoryAttributes['name'], 'subcategories' => $subCategories);
				}
			}
            catch (Exception $ex)
            {
                $messagesBlock = Mage::app()->getLayout()->getBlock('messages');
                $messagesBlock->addError('DutyCalculator API error: ' . $rawXML);
            }
        }
        else
        {
            $messagesBlock = Mage::app()->getLayout()->getBlock('messages');
            $messagesBlock->addError('DutyCalculator API error: No response from server');
        }
		$this->_categories = $categories;
		$this->_suggestedCategories = $suggestedCategories;
		return array('categories' => $this->_categories, 'suggested_categories' => $this->_suggestedCategories);
	}
}