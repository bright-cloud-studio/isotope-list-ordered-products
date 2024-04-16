<?php

namespace Bcs;

use Contao\Database;

class Handler
{
    protected static $arrUserOptions = array();

    public function onProcessForm($submittedData, $formData, $files, $labels, $form)
    {
        if($formData['formID'] == 'bulk_order_csv') {

            if($files == null) {
                echo "NULLs";
            }
            
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

        }
        
    }
    
}
