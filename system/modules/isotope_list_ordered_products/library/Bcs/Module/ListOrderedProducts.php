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
        //$this->Template->test_2 = $this->findProducts();
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
		
		return $objOrder;
		
		if ($objOrder) {
			while($objOrder->next) {
				$objItem = ProductCollectionItem::findBy('pid', $objOrder->id);
				if ($objItem) {
					while ($objItem->next()) {
						if (!in_array($objItem->product_id, $arrIds)) {
							$arrIds[] = $objItem->product_id;
							$arrProducts[] = $objItem->current()->getProduct();
						}
					}
				}
			}
		}

        return $arrProducts;
    }

}
