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
use Contao\Date;
use Contao\Environment;

use Haste\Generator\RowClass;
use Haste\Http\Response\HtmlResponse;
use Haste\Input\Input;
use Haste\Util\Url;

use Isotope\Collection\ProductPrice as ProductPriceCollection;

use Isotope\Model\Product;
use Isotope\Model\ProductCache;
use Isotope\Model\ProductPrice;
use Isotope\Module\ProductList;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollection\Order;
use Isotope\Interfaces\IsotopeProduct;




use Contao\FrontendUser;

class ListOrderedProducts extends ProductList
{
    // Template
    protected $strTemplate = 'mod_iso_list_ordered_products';
    protected $strFormId = 'iso_mod_product_group_list';
    
    public function generate()
    {
		//if($this->enableBatchAdd)
			//$this->strTemplate = 'mod_iso_list_ordered_products_batch';

        return parent::generate();
    }
    
    protected function compile()
    {

        global $objPage;
        $cacheKey      = $this->getCacheKey();
        $arrProducts   = null;
        $arrCacheIds   = null;

        
        
        if($this->enableOrderedFilter) {
            $arrProducts = $this->findOrderedProducts();
        }
        else {
            $arrProducts = $this->findAllProducts();
        }
        
        
        // No products found
        if (!\is_array($arrProducts) || empty($arrProducts)) {
            $this->compileEmptyMessage();

            return;
        }

        $arrBuffer         = array();
        $arrDefaultOptions = $this->getDefaultProductOptions();
        
        //if($this->enableBatchAdd)
		//	$this->iso_list_layout = 'iso_list_ordered_products_batch';
			
		//$this->iso_gallery = 'iso_list_ordered_products_customized';
        

        // Prepare optimized product categories
        $preloadData = $this->batchPreloadProducts();

        /** @var \Isotope\Model\Product\Standard $objProduct */
        foreach ($arrProducts as $objProduct) {
            
            // We may run into instances where a previosuly ordred product no longer exists.
            // In that event, if this product doesnt equal anything then just ignore it
            if($objProduct != null) {
            
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

        //if($this->enableBatchAdd)
		//{				
			$arrButtons['add_to_cart_batch'] = array('label' => $GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback' => array('\Bcs\Frontend\OrderedProductsFrontend', 'addToCartBatch'));
			
			if (\Input::post('FORM_SUBMIT') == $this->getFormId() && !$this->doNotSubmit) {
				foreach ($arrButtons as $button => $data) {
					if (\Input::post($button) != '') {
						if (isset($data['callback'])) {
							$objCallback = \System::importStatic($data['callback'][0]);
							$objCallback->{$data['callback'][1]}($this, $arrConfig);
						}
						break;
					}
				}
			}
		//}

        $this->Template->action        = \ampersand(\Environment::get('request'));
		$this->Template->formId		   = $this->strFormId;
		$this->Template->formSubmit    = $this->strFormId;
        $this->Template->enctype       = 'application/x-www-form-urlencoded';
        $this->Template->buttons 	   = $arrButtons;
        $this->Template->products = $arrBuffer;
        }
    }




    // Custom function that returns an array of products that the user previously ordered
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

                    $arrProducts[] = $tmpItm;
				}
		    }
		}
        
        // Return our templates items/products
        return $arrProducts;
    }
    
    
    
    
    
    
    
    
    
    
    
    // Custom function that returns ALL products that are published
    protected function findAllProducts($arrCacheIds = null)
    {
        // Stores our templated products and their IDs to prevent duplicates
        $arrProducts = [];
		$product_ids = [];
        
        //$objProducts = Product::findPublished();
        $objProducts = Product::findPublishedBy('pid', 0);
        
        
        
        
        // For each order we found
		foreach($objProducts as $product) {
		    
		    // Find any variants
		    
		    $objVariants = Product::findPublishedByPid($product->id);
		    
		    if($objVariants) {
		        foreach($objVariants as $variant) {
		            $variant->is_variant = 1;
		            $arrProducts[] = $variant;
		        }
		    } else 
		        $arrProducts[] = $product;
		    
            //$arrProducts[] = $product;
			
		}
        
        
        // Return our templates items/products
        return $arrProducts;
    }
    

    public function getFormId()
    {
        return $this->strFormId;
    }
    
    
    private function batchPreloadProducts()
    {
        $query = "SELECT c.pid, GROUP_CONCAT(c.page_id) AS page_ids FROM tl_iso_product_category c JOIN tl_page p ON c.page_id=p.id WHERE p.type!='error_403' AND p.type!='error_404'";

        if (!BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $query .= " AND p.published='1' AND (p.start='' OR p.start<'$time') AND (p.stop='' OR p.stop>'" . ($time + 60) . "')";
        }

        $query .= " GROUP BY c.pid";

        $data = ['categories' => [], 'prices' => []];
        $result = Database::getInstance()->execute($query);

        while ($row = $result->fetchAssoc()) {
            $data['categories'][$row['pid']] = explode(',', $row['page_ids']);
        }

        $t = ProductPrice::getTable();
        $arrOptions = [
            'column' => [
                "$t.config_id=0",
                "$t.member_group=0",
                "$t.start=''",
                "$t.stop=''",
            ],
        ];

        /** @var ProductPriceCollection $prices */
        $prices = ProductPrice::findAll($arrOptions);

        if (null !== $prices) {
            foreach ($prices as $price) {
                if (!isset($data['prices'][$price->pid])) {
                    $data['prices'][$price->pid] = $price;
                }
            }
        }

        return $data;
    }


}
