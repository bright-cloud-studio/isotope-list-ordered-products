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

class ListOrderedProducts extends ProductList
{
    // Template
    protected $strTemplate = 'mod_iso_list_ordered_products';

    protected function findProducts($arrCacheIds = null)
    {
        $objUser = FrontendUser::getInstance();

        $arrProducts = [];

        return $arrProducts;
    }

}
