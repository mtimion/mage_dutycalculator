<?php
/**
 * Product tags admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Dutycalculator_Charge_Adminhtml_RapidController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {

        $this->loadLayout()
            ->_setActiveMenu('catalog/adminhtml_rapid')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('DutyCalculator Rapid Classification tool'), Mage::helper('adminhtml')->__('Import/Export'));
        return $this;
    }

    public function indexAction()
    {
		$this->_title($this->__('DutyCalculator Rapid Classification tool'));
        $this->_initAction();
		$exportMessagesBlock = $this->getLayout()->getBlock('export.messages');
		$exportMessages = Mage::registry('export_messages');
		if (is_array($exportMessages))
		{
			foreach ($exportMessages as $exportMessage)
			{
				switch ($exportMessage['type'])
				{
					case 'error':
						$exportMessagesBlock->addError($exportMessage['message']);
						break;
					case 'success':
						$exportMessagesBlock->addSuccess($exportMessage['message'], false);
						break;
					case 'notice':
						$exportMessagesBlock->addNotice($exportMessage['message'], false);
						break;
				}
			}
		}
		$importMessagesBlock = $this->getLayout()->getBlock('import.messages');
		$importMessages = Mage::registry('import_messages');
		if (is_array($importMessages))
		{
			foreach ($importMessages as $importMessage)
			{
				switch ($importMessage['type'])
				{
					case 'error':
						$importMessagesBlock->addError($importMessage['message']);
						break;
					case 'success':
						$importMessagesBlock->addSuccess($importMessage['message'], false);
						break;
					case 'notice':
						$importMessagesBlock->addNotice($importMessage['message'], false);
						break;
				}
			}
		}
		$importMessagesBlock->addNotice($this->__('Total size of uploadable files must not exceed %sM', $this->_bytesToMbytes($this->_getUploadMaxFilesize())), false);
		$this->renderLayout();
    }

	public function exportAction()
	{
		if (!$this->getRequest()->getPost('export_option'))
		{
			return $this->_redirect('*/*/index');
		}
		$messages = array();
		try
		{
			set_time_limit(0);
			ini_set('memory_limit', '512M');
			/* @var $products Mage_Catalog_Model_Resource_Product_Collection */
			$products = Mage::getResourceModel('catalog/product_collection')->setStoreId(0);
			if ($this->getRequest()->getPost('export_option') == Dutycalculator_Charge_Helper_Data::DC_EXPORT_ONLY_PRODUCTS_WITHOUT_DC_ID)
			{
				$products->addAttributeToFilter('dc_product_id', array(array('null' => null), array('eq' => 0)), 'left');
			}
			if ($this->getRequest()->getPost('only_active', 0))
			{
				$products->addAttributeToFilter('status', array(array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)));
			}
			$products->addAttributeToSelect('name');
			$products->addAttributeToSelect('visibility');
			$products->addAttributeToSelect('description');
			$additionalAttributes = explode(',', Mage::getStoreConfig('dc_charge_extension/dccharge/additional-attributes-for-rapid-classification'));
			/* @var $attributesCollection Mage_Eav_Model_Resource_Entity_Attribute_Collection */
			$attributesCollection = Mage::getModel('eav/entity_attribute')->getResourceCollection();
			$attributesCollection->setCodeFilter($additionalAttributes);
			$attributesCollection->load();
			$additionalAttributes = array();
			foreach ($attributesCollection as $additionalAttribute)
			{
				$additionalAttributes[$additionalAttribute->getAttributeCode()] = $additionalAttribute->getFrontendLabel();
				$products->addAttributeToSelect($additionalAttribute->getAttributeCode());
			}

			if ($this->getRequest()->getPost('only_parent', 0))
			{
				/* @var $relation Mage_Catalog_Model_Resource_Product_Relation */
				$relation = Mage::getResourceSingleton('catalog/product_relation');
				$products->getSelect()->joinLeft(array('rel' => $relation->getTable('catalog/product_relation')), 'e.entity_id = rel.child_id', array('children_id' => 'rel.child_id'));//->where('rel.child_id is null or (rel.child_id is not null and e.visibility != ?)', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
			}
			$products->getSelect()->group('e.entity_id');
			$products->load();

			if ($products->getSize() > 0)
			{
				$fileName = date('Y-m-d') . '-exported-products-for-dc.csv';
				$fp = fopen(Mage::helper('dccharge')->getWorkingDir() . $fileName, 'w');
				$row = array('SKU', 'Product Title', 'Description', 'Url');
				fputcsv($fp, $row, ',', '"');
				foreach ($products as $product)
				{
					if ($product->getData('children_id') && $product->getData('visibility') == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
					{
						continue;
					}
					/* @var $product Mage_Catalog_Model_Product */
					/* @var $categories Mage_Catalog_Model_Category */
					$categoryId = current($product->getCategoryIds());
					$categoriesToAdd = '';
					if ($categoryId)
					{
						while (true)
						{
							$category = Mage::getSingleton('catalog/category');
							$category->load($categoryId);
							$categoryId = $category->getData('parent_id');
							if (!$categoryId)
							{
								break;
							}
							$categoriesToAdd = $category->getName() . ' / ' . $categoriesToAdd;
						}
					}


					$title = $categoriesToAdd . $product->getName();
					$description = $product->getDescription();
					$additionalDescription = '';
					foreach ($additionalAttributes as $attrCode => $attrLabel)
					{
						if ($product->getAttributeText($attrCode))
						{
							$additionalDescription .= $attrLabel . ': ' . $product->getAttributeText($attrCode) . ', ';
						}
						else if ($product->getData($attrCode))
						{
							$additionalDescription .= $attrLabel . ': ' . $product->getData($attrCode) . ', ';
						}
					}
					if (strlen($additionalDescription))
					{
						$description .= "\n" . rtrim($additionalDescription, ', ');
					}
					$row = array($product->getSku(), $title, $description, $product->getProductUrl());
					fputcsv($fp, $row, ',', '"');
				}
				fclose($fp);
				$fileContent = file_get_contents(Mage::helper('dccharge')->getWorkingDir() . $fileName);
				@unlink(Mage::helper('dccharge')->getWorkingDir() . $fileName);
				return $this->_prepareDownloadResponse(
					$fileName,
					$fileContent,
					'text/csv'
				);
			}
			else
			{
				$messages[] = array('type' => 'notice', 'message' => $this->__('There are no products with selected search criteria.'));
			}
		}
		catch (Exception $ex)
		{
			Mage::logException($ex);
			$messages[] = array('type' => 'error', 'message' => 'Some error occured. Please try again (' . $ex->getMessage() . ').');
		}
		Mage::register('export_messages', $messages);
		return $this->_forward('index');
	}

	public function importAction()
	{
		set_time_limit(0);
		ini_set('memory_limit', '512M');
		$messages = array();
		if (isset($_FILES['import_file']) && $_FILES['import_file']['size'] > 0 && file_exists($_FILES['import_file']['tmp_name']))
		{
			$file = $_FILES['import_file'];
			$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
			if (strtolower($extension) == 'csv')
			{
				if (move_uploaded_file($file['tmp_name'], Mage::helper('dccharge')->getWorkingDir() . $file['name']))
				{
					$rows = 0;
					$fp = fopen(Mage::helper('dccharge')->getWorkingDir() . $file['name'], 'r+');
					if ($fp)
					{
						fgetcsv($fp, 1024, ","); //skip first row
						$dataForUpdate = array();
						while (($data = fgetcsv($fp, 1024, ",")) !== false)
						{
							/* @var $product Mage_Catalog_Model_Product */
							$product = Mage::getResourceModel('catalog/product');
							if (!isset($dataForUpdate[(int)$data[1]]))
							{
								$dataForUpdate[(int)$data[1]] = array();
							}
							$dataForUpdate[(int)$data[1]][] = (string)$data[0];
							$rows++;
						}
						foreach ($dataForUpdate as $dcProductId => $skuArray)
						{
							/* @var $products Mage_Catalog_Model_Resource_Product_Collection */
							$products = Mage::getResourceModel('catalog/product_collection')->setStoreId(0);
							$products->addAttributeToSelect('dc_product_id');
							$products->addAttributeToFilter('sku', $skuArray);
							$products->load();
							$products->setDataToAll('dc_product_id', $dcProductId);
							$products->save();
							unset($products);
						}
						$cache = Mage::getSingleton('core/cache');
						$cache->clean(array('dutycalculator'));
						Mage::app()->getCacheInstance()->cleanType('collections');
						$messages[] = array('type' => 'success', 'message' => $this->__('File has been imported successfully. %s ' . ($rows > 1 ? 'products have' : 'product has' ) . ' been updated.', $rows));
					}
					else
					{
						$messages[] = array('type' => 'error', 'message' => $this->__('Can not open uploaded file to read.'));
					}
				}
				else
				{
					$messages[] = array('type' => 'error', 'message' => $this->__('Can not move uploaded file to %s. Please check folder permissions.', Mage::helper('dccharge')->getWorkingDir()));
				}
			}
			else
			{
				$messages[] = array('type' => 'error', 'message' => $this->__('Please select a CSV file for upload.'));
			}
		}
		else
		{
			$messages[] = array('type' => 'error', 'message' => $this->__('Please select a CSV file for upload.'));
		}
		Mage::register('import_messages', $messages);
		return $this->_forward('index');
	}

	/**
	 * Max upload filesize in bytes
	 *
	 * @return int
	 */
	protected function _getUploadMaxFilesize()
	{
		return min($this->_getBytesIniValue('upload_max_filesize'), $this->_getBytesIniValue('post_max_size'));
	}

	/**
	 * Return php.ini setting value in bytes
	 *
	 * @param string $ini_key php.ini Var name
	 * @return int Setting value
	 */
	protected function _getBytesIniValue($ini_key)
	{
		$_bytes = @ini_get($ini_key);

		// kilobytes
		if (stristr($_bytes, 'k')) {
			$_bytes = intval($_bytes) * 1024;
			// megabytes
		} elseif (stristr($_bytes, 'm')) {
			$_bytes = intval($_bytes) * 1024 * 1024;
			// gigabytes
		} elseif (stristr($_bytes, 'g')) {
			$_bytes = intval($_bytes) * 1024 * 1024 * 1024;
		}
		return (int)$_bytes;
	}

	/**
	 * Simple converrt bytes to Megabytes
	 *
	 * @param int $bytes
	 * @return int
	 */
	protected function _bytesToMbytes($bytes)
	{
		return round($bytes / (1024 * 1024));
	}
}