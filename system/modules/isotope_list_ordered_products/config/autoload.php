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


// Register Classes
ClassLoader::addClasses(array
(
    'Bcs\Module\ListOrderedProducts'         => 'system/modules/isotope_list_ordered_products/library/Bcs/Module/ListOrderedProducts.php',
    'Bcs\Frontend\OrderedProductFrontend'    => 'system/modules/isotope_list_ordered_products/library/Bcs/Frontend/OrderedProductFrontend.php'
));
