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

    /* Called when clicking the "Add to Cart" button */
    public function addToCartBatch($objModule, array $arrConfig = array())
    {   
        
        /* If we have CSV data in our form */
        if (\Input::post('csv_data') != "")
		{

            /* Tracks if we have added any products or not within this function */
            $blnAdded = false;
            
		    // Convert the data string into a PHP array
		    $str_csv = str_getcsv(\Input::post('csv_data'),',');
		    // Break that array into chunks of 2 (sku,quantity)
		    $chunks = array_chunk($str_csv, 2);
		    
		    // Loop through our array csv array
            foreach($chunks as $prod)
            {

                /* If the quantity is entered as 0, coninue on */
                if(intval($prod[1])==0)
    			    continue;

                /* Find product by SKU */
                //$objProduct = Product::findPublishedBy('sku', array($prod[0]));
                //$objProduct = Product::findByPk($id);
                //$objProduct = Product::findBy(['sku' => $prod[0]]);
                //$objProducts = Product::findBy(['tl_iso_product.sku=?'], [$prod[0]]);

                $prod = Product::findOneBy(['tl_iso_product.sku=?'],[$prod[0]]);
                if($prod != null) {
                
                    // If there is no error after adding this product to the cart
                    if (Isotope::getCart()->addProduct($prod, $prod[1], $arrConfig) !== false)
                        $blnAdded = true;
                }

                
            }

            if($blnAdded) {
                $_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['addedToCartBatch'];
            
                if (!$objModule->iso_addProductJumpTo) {
                    $this->reload();
                }
            
                \Controller::redirect(\Haste\Util\Url::addQueryString('continue=' . base64_encode(\Environment::get('request')), $objModule->iso_addProductJumpTo));
            }
		    
		    
		    
		    /* Debug */
		    //echo '<pre>';
		    //var_dump($chunks);
		    //echo '</pre>';
		    
		    die();
		}
        
        
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
