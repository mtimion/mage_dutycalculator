<?php
/**
 * Category.php created by a.voytik.
 * Date: 12/03/2012 07:38
 */
class Dutycalculator_Charge_Block_Adminhtml_Category extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element
{
	public function getElementHtml()
	{
		$model = Mage::getModel('dccharge/categories');
		$result = $model->getCategories();
		$categories = $result['categories'];
		$suggestedCategories = $result['suggested_categories'];
		$categoriesBlock = Mage::app()->getLayout()->createBlock('adminhtml/template');
		$categoriesBlock->setTemplate('dccharge/categories.phtml');
		$categoriesBlock->setCategories($categories);
		$categoriesBlock->setSuggestedCategories($suggestedCategories);
		$categoriesBlock->setName($this->getElement()->getName());
		$product = Mage::registry('current_product');
		$formattedProductParams = '';
		if ($product->getId())
		{
			$product->load($product->getId());
			$dcProductId = $product->getDcProductId();
			if ($dcProductId)
			{
				foreach ($categories as $category)
				{
					if (isset($category['subcategories']) && is_array($category['subcategories']))
					{
						foreach ($category['subcategories'] as $subCategory)
						{
							if (isset($subCategory['items']) && is_array($subCategory['items']))
							{
								foreach ($subCategory['items'] as $item)
								{
									if ($item['value'] == $dcProductId)
									{
										$categoriesBlock->setDcCategoryId($category['value']);
										$categoriesBlock->setDcSubcategoryId($subCategory['value']);
										$categoriesBlock->setDcProductId($product->getDcProductId());
										break 3;
									}
								}
							}
						}
					}
				}
			}
			$description = '';
			if (strlen(strip_tags($product->getDescription())) < 512)
			{
				$description = strip_tags($product->getDescription());
			}
			else if (strlen(strip_tags($product->getShortDescription())) < 512)
			{
				$description = strip_tags($product->getShortDescription());
			}
			else
			{
				$description = substr(strip_tags($product->getDescription()), 0, 512);
			}
			$formattedProductParams = '?product_name=' . urlencode($product->getName()) . '&product_description=' . urlencode($description) . '&product_url=' . urlencode($product->getProductUrl()) . '&product_price=' . number_format($product->getPrice(), 2, '.', false);
			if ($product->getCountryOfManufacture())
			{
				$formattedProductParams .= '&product_country_of_origin=' . urlencode($product->getCountryOfManufacture());
			}

		}
		$categoriesBlock->setProductParams($formattedProductParams);
		return $categoriesBlock->_toHtml();
	}
}