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

use Contao\Database;
use Contao\Environment;

use Haste\Generator\RowClass;
use Haste\Input\Input;
use Haste\Util\Url;

use Isotope\Model\Product;
use Isotope\Module\ProductList;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollection\Order;
use Isotope\Interfaces\IsotopeProduct;


use Isotope\Model\ProductCache;

use Contao\FrontendUser;

class ListOrderedProducts extends ProductList
{
    // Template
    protected $strTemplate = 'mod_iso_list_ordered_products';
    
    protected function compile()
    {

        global $objPage;
        $cacheKey      = $this->getCacheKey();
        $arrProducts   = null;
        $arrCacheIds   = null;


        $arrProducts = $this->findOrderedProducts();

        // No products found
        if (!\is_array($arrProducts) || empty($arrProducts)) {
            $this->compileEmptyMessage();

            return;
        }

        $arrBuffer         = array();
        $arrDefaultOptions = $this->getDefaultProductOptions();

        // Prepare optimized product categories
        //$preloadData = $this->batchPreloadProducts();

        /** @var \Isotope\Model\Product\Standard $objProduct */
        foreach ($arrProducts as $objProduct) {
            
            
            /*
            if ($objProduct instanceof Product\Standard) {
                if (isset($preloadData['categories'][$objProduct->id])) {
                    $objProduct->setCategories($preloadData['categories'][$objProduct->id], true);
                }
                if (!$objProduct->hasAdvancedPrices()) {
                    if ($objProduct->hasVariantPrices() && !$objProduct->isVariant()) {
                        $ids = $objProduct->getVariantIds();
                    } else {
                        $ids = [$objProduct->hasVariantPrices() ? $objProduct->getId() : $objProduct->getProductId()];
                    }

                    $prices = array_intersect_key($preloadData['prices'], array_flip($ids));

                    if (!empty($prices)) {
                        $objProduct->setPrice(new ProductPriceCollection($prices, ProductPrice::getTable()));
                    }
                }
            }
            */

            $arrConfig = $this->getProductConfig($objProduct);

            if (Environment::get('isAjaxRequest')
                && Input::post('AJAX_MODULE') == $this->id
                && Input::post('AJAX_PRODUCT') == $objProduct->getProductId()
                && !$this->iso_disable_options
            ) {
                $content = $objProduct->generate($arrConfig);
                $content = Controller::replaceInsertTags($content, false);

                throw new ResponseException(new Response($content));
            }

            $objProduct->mergeRow($arrDefaultOptions);

            // Must be done after setting options to generate the variant config into the URL
            if ($this->iso_jump_first && Input::getAutoItem('product', false, true) == '') {
                throw new RedirectResponseException($objProduct->generateUrl($arrConfig['jumpTo'], true));
            }

            $arrBuffer[] = array(
                'cssID'     => $objProduct->getCssId(),
                'class'     => $objProduct->getCssClass(),
                'html'      => $objProduct->generate($arrConfig),
                'product'   => $objProduct,
            );
        }

        // HOOK: to add any product field or attribute to mod_iso_productlist template
        if (isset($GLOBALS['ISO_HOOKS']['generateProductList'])
            && \is_array($GLOBALS['ISO_HOOKS']['generateProductList'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['generateProductList'] as $callback) {
                $arrBuffer = System::importStatic($callback[0])->{$callback[1]}($arrBuffer, $arrProducts, $this->Template, $this);
            }
        }

        RowClass::withKey('class')
            ->addCount('product_')
            ->addEvenOdd('product_')
            ->addFirstLast('product_')
            ->addGridRows($this->iso_cols)
            ->addGridCols($this->iso_cols)
            ->applyTo($arrBuffer);


        // Create our Add all products button to the template
		$arrButtons['add_to_cart_all'] = array('label' => $GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback' => array('\Bcs\Frontend\OrderedProductsFrontend', 'addToCartAll'));
		$this->Template->buttons 	   = $arrButtons;
		
		
        $this->Template->products = $arrBuffer;
        
    }

    protected function findOrderedProducts($arrCacheIds = null)
    {
        
        // Get our front end user
        $objUser = FrontendUser::getInstance();
        
        // If we dont have a user, return
        if (!$objUser) {
			return [];
		}

        // Stores our templated products and their IDs to prevent duplicates
        $arrProducts = [];
		$arrIds = [];
		
		// Find all Collections with our member ID and a non-blank 'document_number'
		$arrFind = array(
			'column' 	=> array("member=?", "document_number!=''"),
			'value'		=> $objUser->id
		);
		$objOrder = Order::findAll($arrFind);
		
		// For each order we found
		foreach($objOrder as $order) {
		    // Get all the items, AKA products, in our collection
		    $objItem = ProductCollectionItem::findBy('pid', $order->id);
		    // For each of the items we found
		    foreach($objItem as $item) {
		        
		        // If this product id is not already in our saved id numbers
		        if (!in_array($item->product_id, $arrIds)) {
		            
		            
		            // Add to our stored ids to prevent duplicates
					$arrIds[] = $item->product_id;
					// Store a temporary copy of the item/product
					$tmpItm = $item->current()->getProduct();
					
					
					/*
					// Assign our values
					$arrProd = array();
					$arrProd['id'] 			= $tmpItm->id;
				    $arrProd['name'] 		= $tmpItm->name;
				    $arrProd['add_to_cart'] = Url::addQueryString('add_to_cart=' . $tmpItm->id);
				    $arrProd['add_to_cart_2'] = "testy";
                    
                    
                    // Template our saved values as an 'item_ordered_product'
                    //$strItemTemplate = ($this->locations_customItemTpl != '' ? $this->locations_customItemTpl : 'item_rep');
                    $strItemTemplate = 'item_ordered_product';
                    $objTemplate = new \FrontendTemplate($strItemTemplate);
                    $objTemplate->setData($arrProd);
                    $arrProducts[] = $objTemplate->parse();
                    
                    */
                    
                    $arrProducts[] = $tmpItm;
				}
		    }
		}
        
        // Return our templates items/products
        return $arrProducts;
    }

}
