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

  
namespace Bcs\Module;

use Isotope\Module\ProductList;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollection\Order;
use Isotope\Interfaces\IsotopeProduct;

use Contao\FrontendUser;

class ListOrderedProducts extends ProductList
{
    // Template
    protected $strTemplate = 'mod_iso_list_ordered_products';
    
    protected function compile() {
        //parent::compile();
        $this->Template->ordered_products = $this->findProducts();
    }

    protected function findProducts($arrCacheIds = null)
    {
        
        // Get our front end user
        $objUser = FrontendUser::getInstance();
        
        // If we dont have a user, return
        if (!$objUser) {
			return [];
		}

        $arrProducts = [];
		$arrIds = [];
		
		$arrFind = array(
			'column' 	=> array("member=?", "document_number!=''"),
			'value'		=> $objUser->id
		);
		
		$objOrder = Order::findAll($arrFind);
		
		
		foreach($objOrder as $order) {
		    $objItem = ProductCollectionItem::findBy('pid', $order->id);
		    foreach($objItem as $item) {
		        if (!in_array($item->product_id, $arrIds)) {
					$arrIds[] = $item->product_id;
					$arrProducts[] = $item->current()->getProduct();
				}
		    }
		}

        return $arrProducts;
    }

}
