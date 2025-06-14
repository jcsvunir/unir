<?php

include_once __DIR__ . DIRECTORY_SEPARATOR . 'SDK' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'SDK' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'omv1_boostrap.php';

$options['idCustomer'] = "<customer_id>"; // ID del cliente
$options['year'] = "<year>"; // Año de la factura
$options['month'] = "<mes>"; // Mes de la factura

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

        $csvString = $invoice->__toCSV();

        print_r($csvString);

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


