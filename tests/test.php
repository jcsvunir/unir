<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'SDK' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'SDK' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'omv1_boostrap.php';

$options['idCustomer'] = "c5dcc14d5398585.34505544";
$options['year'] = "2022";
$options['month'] = "05";

try {
    // Crear objeto factura
    $invoice = new \Billing\OMV1\OMV1InvoiceCyclePeriod($options);

    //Calcular factura
    $invoice->doInvoice();

    $invoiceArray = $invoice->getInvoiceArray();
    if ($invoice->hasIssues()) {

        echo "\nTotal errors issues found: " . $invoice->getInvoiceIssues()->getTotalErrors() . "\n";
        foreach ($invoice->getInvoiceIssues()->getErrors() as $item) {
            print_r($item);
        }

        echo "\nTotal warnings issues found: " . $invoice->getInvoiceIssues()->getTotalWarnings() . "\n";
        foreach ($invoice->getInvoiceIssues()->getWarnings() as $item) {
            print_r($item);
        }

        echo "\nTotal infos issues found: " . $invoice->getInvoiceIssues()->getTotalInfos() . "\n";
        foreach ($invoice->getInvoiceIssues()->getInfos() as $item) {
            print_r($item);
        }

    } else {
        $invoiceArray = $invoice->getInvoiceArray();
        //print_r($invoiceArray);
        // Save invoice on DB, return invoiceID
        //$invoiceId = $invoice->save(true);
        // Save invoice on GCS in CSV format
        // Step#1: get CSV on string format from invoice in array format.
        $csvString = $invoice->__toCSV();

        print_r($csvString);
        echo "\n";
        // Step#2: save CSV string on GCS.
        //$invoice->saveCSV($csvString,true);
        // Verify if exists CSV on GCS
        if ($invoice->existsCSV()) {
            echo "\nExists CSV file for this invoice stored on GCS.\n";
        }

        // retrieve CSV string from GCS.
        echo $invoice->getCSVString();

        // Download CSV file from GCS.
        //$invoice->downloadCSVFile();

    }

} catch (Exception $exception) {
    print_r($exception->getMessage());
}


