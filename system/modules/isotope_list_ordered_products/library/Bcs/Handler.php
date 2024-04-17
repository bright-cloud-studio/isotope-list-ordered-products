<?php

namespace Bcs;

use Contao\Database;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Model\Attribute;
use Isotope\Model\AttributeOption;

use Isotope\Model\Product;

class Handler
{
    protected static $arrUserOptions = array();

    public function onProcessForm($submittedData, $formData, $files, $labels, $form)
    {
        if($formData['formID'] == 'bulk_order_csv') {


            /* Files is not null, we have an upload submission */
            if($files != null) {
                echo "FILE FOUND<br>";
                
                // Get the Contao file
                $csv = \FilesModel::findByUuid($files['csv_upload']['uuid']);
                // Get the URL using the Contao file's UUID
                $url = 'https://mossnutrition.brightcloudstudioserver.com/' . $csv->path;
                // Convert the Contao file into a PHP file
                $file = fopen($url,"r");
                
                // Load our file and turn it into a php array
                $str_csv = fgetcsv($file);
                // Break that array into chunks of 2 (sku,quantity)
                $chunks = array_chunk($str_csv, 2);
                
                echo "<pre>";
                print_r($chunks);
                echo "</pre>";
                
                die();
            } else if($submittedData['csv_string'] != "") {

                /* Tracks if we have added any products or not within this function */
                $blnAdded = false;

                // Convert the data string into a PHP array
		        $str_csv = str_getcsv($submittedData['csv_string'],',');
		        // Break that array into chunks of 2 (sku,quantity)
		        $chunks = array_chunk($str_csv, 2);
                
                // Loop through our array csv array
                foreach($chunks as $prod)
                {
    
                    /* If the quantity is entered as 0, coninue on */
                    if(intval($prod[1])==0)
        			    continue;
    
                    /* Find product by SKU */
                    $objProd = Product::findOneBy(['tl_iso_product.sku=?'],[$prod[0]]);

                    echo "<pre>";
                    print_r($objProd);
                    echo "</pre>";
                    die();
                    
                    
                    /* If we found a product */
                    if($objProd != null) {
                    
                        // If there is no error after adding this product to the cart
                        if (Isotope::getCart()->addProduct($objProd, $prod[1], $arrConfig) !== false)
                            $blnAdded = true;
                    }
                    
                }
    
                /* If we have added a product to the cart */
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
    
}
