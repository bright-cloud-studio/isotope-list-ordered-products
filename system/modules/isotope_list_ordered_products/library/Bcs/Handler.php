<?php

namespace Bcs;

use Contao\Database;

class Handler
{
    protected static $arrUserOptions = array();

    public function onProcessForm($submittedData, $formData, $files, $labels, $form)
    {

        echo "HIT";
        die();

        if($formData['formID'] == 'bulk_order_csv') {

          echo "BING!!!";
          die();

        }
    }
}
