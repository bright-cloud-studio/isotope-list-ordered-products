<?php

/**
 * Bright Cloud Studio's Isotope List Ordered Products
 *
 * Copyright (C) 2023 Bright Cloud Studio
 *
 * @package    bright-cloud-studio/isotope-list-ordered-products
 * @link       https://www.brightcloudstudio.com/
 * @license    http://opensource.org/licenses/lgpl-3.0.html
**/


namespace Bcs\Frontend;

use Haste\Haste;
use Isotope\Isotope;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;


class OrderedProductsFrontend extends \Isotope\Frontend
{
	    /**
     * Callback for add_to_cart_batch button
     * @param object
     * @param array
     */
    public function addToCartBatch($objModule, array $arrConfig = array())
    {   
		$blnAdded = false;    		
		
		if (\Input::post('quantities') != "")
		{
			$blnAdded = false; 
			$strQuantities = \Input::post('quantities');
			$arrQuantity = array();
			$arrTemp = explode(";", $strQuantities);
			foreach ($arrTemp as $temp) {
				list($product_id, $quantity) = explode(",", $temp);
				$arrQuantity[$product_id] = $quantity;
			}
		} 
			
		if (\Input::post('quantities') == "" || empty($arrQuantity))
		{		
			$blnAdded = false;    		
			$arrQuantity = \Input::post('quantity_requested');
		}

		if(is_array($arrQuantity))
		{
			
			foreach($arrQuantity as $id=>$quantity)
			{
				if(intval($quantity)==0)
					continue;
					
				$objProduct = Product::findByPk($id);
				
				if (Isotope::getCart()->addProduct($objProduct, $quantity, $arrConfig) !== false)
					$blnAdded = true;
			}
			
			if($blnAdded) {
				$_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['addedToCartBatch'];
	
				if (!$objModule->iso_addProductJumpTo) {
					$this->reload();
				}
	
				\Controller::redirect(\Haste\Util\Url::addQueryString('continue=' . base64_encode(\Environment::get('request')), $objModule->iso_addProductJumpTo));
			}
		}
    }

}
