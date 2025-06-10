<?php


include_once __DIR__ . DIRECTORY_SEPARATOR . 'SDK' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR;





try {
    // Generate invoice
    $invoice = new \Billing\OMV1\OMV1InvoiceCyclePeriod("c5d4189f03153b0.28092902", "2019", "08");

    //Calcular factura
    $invoice->doInvoice();

    if ($invoice->hasIssues()){

        echo "\nTotal errors issues found: " . $invoice->getInvoiceIssues()->getTotalErrors() . "\n";
        foreach($invoice->getInvoiceIssues()->getErrors() as $item){
            print_r($item);
        }

        echo "\nTotal warnings issues found: " . $invoice->getInvoiceIssues()->getTotalWarnings() . "\n";
        foreach($invoice->getInvoiceIssues()->getWarnings() as $item){
            print_r($item);
        }

        echo "\nTotal infos issues found: " . $invoice->getInvoiceIssues()->getTotalInfos() . "\n";
        foreach($invoice->getInvoiceIssues()->getInfos() as $item){
            print_r($item);
        }

    }else{

        $csvString = $invoice->__toCSV();

        // Step#2: save CSV string on GCS.
        $invoice->saveCSV($csvString,true);

        // Download CSV file from GCS.
        $invoice->downloadCSVFile();

    }

}catch (\Exceptions\InvoiceIssuesException $exception){
    print_r($exception->getMessage());
}catch(\Exceptions\ObjectAlreadyExistsException $exception){
    print_r($exception->getMessage());
}catch(\Exceptions\InvoiceAlreadyExistsException $exception){
    print_r($exception->getMessage());
}catch(\Exceptions\ObjectNotExistsException $exception){
    print_r($exception->getMessage());
}catch (\Exceptions\DBErrorException $exception){
    print_r($exception->getMessage());
}catch (\Exceptions\InvoiceBillingProcessException $exception){
    print_r($exception->getMessage());
}


