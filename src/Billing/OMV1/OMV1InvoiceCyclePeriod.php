<?php

namespace Billing\OMV1;

use Billing\AbstractInvoiceCyclePeriod;
use Billing\InvoiceIssue;
use Billing\IssueEnumerator;
use Billing\SeverityEnumerator;
use Core\Timer;
use Exceptions\DBErrorException;
use Exceptions\InvoiceAlreadyExistsException;
use Exceptions\InvoiceIssuesException;
use Exceptions\UndefinedCountryNameException;
use Exceptions\UndefinedPLMNNameException;
use Exceptions\UnitChargeNotFoundException;
use Models\OMV1\Invoice;
use Models\OMV1\InvoiceDetail;

class OMV1InvoiceCyclePeriod extends AbstractInvoiceCyclePeriod
{

    use DataRoutine;

    /**
     * @param $options
     * @throws \Exception
     */
    public function __construct($options)
    {

        $options['accountId'] = $this->getCustomerAccountID($options['idCustomer']);
        parent::__construct($options);

    }

    private function getPLMNName($document){
        try{
            $plmnName = $this->getNetworkOperator($document->_id);
        }catch (UndefinedPLMNNameException $undefinedPLMNNameException){
            $plmnName = IssueEnumerator::UNDEFINED;
            $this->issuesArray->add(new InvoiceIssue(IssueEnumerator::INVALID_PLMN_NAME, SeverityEnumerator::ERROR, "Unable to find PLMN name for networkID#" . $document->_id, $this->idCustomer, $this->period));
        }
        return $plmnName;
    }
    private function getCountryName($document):string{
        try{
            $countryName = $this->getCountryName($document->_id);
        }catch (UndefinedCountryNameException $countryNameException){
            $countryName = IssueEnumerator::UNDEFINED;
            $this->issuesArray->add(new InvoiceIssue(IssueEnumerator::UNDEFINED_COUNTRY, SeverityEnumerator::ERROR, "Unable to find country name for networkID#" . $document->_id, $this->idCustomer, $this->period));
        }
        return $countryName;
    }
    private function getUnitCharge($document){
        try {

            $unitCharge = $this->getPeriodUnitCharge($this->idCustomer, $document->_id, $this->period);

        }catch (UnitChargeNotFoundException $unitChargeNotFoundException){
            $unitCharge = IssueEnumerator::INVALID_UNIT_CHARGE_AMOUNT;
            $this->issuesArray->add(new InvoiceIssue(IssueEnumerator::UNIT_CHARGE_DOES_NOT_EXISTS, SeverityEnumerator::ERROR, "Unable to find unit charge for networkID#" . $document->_id, $this->idCustomer, $this->period));
        }
        return $unitCharge;
    }
    /**
     * @param $document
     * @return array
     */
    public function computeConsumption($document):array{
        // Get unit charge
        $unitCharge = $this->getUnitCharge($document);

        return array(   OMV1CSVHeaderConst::HDR_BILL_CYCLE_PERIOD => $this->year . $this->month,
                        OMV1CSVHeaderConst::HDR_CUSTOMER_ID => $this->getCustomerAccountID($this->idCustomer),
                        OMV1CSVHeaderConst::HDR_CUSTOMER_NAME => $this->getCustomerName($this->idCustomer),
                        OMV1CSVHeaderConst::HDR_PLMN_NAME => $this->getPLMNName($document->_id),
                        OMV1CSVHeaderConst::HDR_MCCMNC => $document->_id,
                        OMV1CSVHeaderConst::HDR_COUNTRY_NAME => $this->getCountryName($document),
                        OMV1CSVHeaderConst::HDR_UNIT_CHARGE_MB => $unitCharge,
                        OMV1CSVHeaderConst::HDR_USAGE_VOLUME_KB => $document->usageVolume,
                        OMV1CSVHeaderConst::HDR_TOTAL_CHARGE => $unitCharge * ($document->usageVolume / 1024));
    }


    /**
     * Almacena la factura en la base de datos.
     * @param bool $overWriteIfExists
     * @return string
     * @throws DBErrorException
     * @throws InvoiceAlreadyExistsException
     * @throws InvoiceIssuesException
     */
    public function save($overWriteIfExists = false): string
    {
        $timer = new Timer();

        $removeSuccess = true;

        if ($this->hasIssues()){
            throw new InvoiceIssuesException();
        }

        $data = $this->getInvoiceArray();
        $beginBillPeriod = $this->getBeginBillPeriod();
        $endBillPeriod = $this->getEndBillPeriod();

        $invoiceRecord = Invoice::where('id_customer', '=', $this->idCustomer)
            ->where('begin_bill_period', '=', $beginBillPeriod)
            ->where('end_bill_period', '=', $endBillPeriod)
            ->get();

        // Si existe la misma factura
        if ($invoiceRecord->isNotEmpty()) {

            $invoice = $invoiceRecord->first();
            $invoiceId = $invoice->id_invoice;

            if (!$overWriteIfExists) {
                throw new InvoiceAlreadyExistsException($invoiceId);
            }else{
                // Eliminar factura
                $removeSuccess = $this->removeInvoice($invoiceId);
            }
        }

        if ($removeSuccess){
            $idTAX = $this->getCustomerVATId($this->idCustomer);
            $invoiceId = uniqid("", true);
            $invoice = new Invoice();
            $invoice->id_invoice = $invoiceId;
            $invoice->id_customer = $this->idCustomer;
            $invoice->id_tax = $idTAX;
            $this->getInvoiceSummary($data, $idTAX, $subTotal, $taxTotal, $invoiceTotal);
            $invoice->sub_total = $subTotal;
            $invoice->tax_total = $taxTotal;
            $invoice->total_invoice = $invoiceTotal;
            $invoice->invoice_date = $timer->getDate();
            $invoice->due_date = $timer->getDueDate();
            $invoice->begin_bill_period = $beginBillPeriod;
            $invoice->end_bill_period = $endBillPeriod;
            $invoice->proforma = 0;
            $invoice->visible = 0;
            $invoice->canceled = 0;
            $invoice->review = 0;
            $invoice->manual = 0;
            $invoice->corrective = 0;

            // Guardamos factura
            if ($invoice->save()) {
                foreach ($data as $item) {
                    $invoiceDetail = new InvoiceDetail();
                    $invoiceDetail->id_details = uniqid("", true);
                    $invoiceDetail->id_invoice = $invoiceId;
                    $invoiceDetail->description = $item[OMV1CSVHeaderConst::HDR_PLMN_NAME] . " (" . $item[OMV1CSVHeaderConst::HDR_COUNTRY_NAME] . ")";
                    $invoiceDetail->quantity = $item[OMV1CSVHeaderConst::HDR_USAGE_VOLUME_KB];
                    $invoiceDetail->unit_price = $item[OMV1CSVHeaderConst::HDR_UNIT_CHARGE_MB];
                    $invoiceDetail->total = $item[OMV1CSVHeaderConst::HDR_TOTAL_CHARGE];
                    if (!$invoiceDetail->save()){
                        throw new DBErrorException("Errors found inserting invoice details for Invoice#$invoiceId");
                    }
                }
            } else {
                throw new DBErrorException("Errors found inserting new Invoice.");
            }
        }else{
            throw new DBErrorException("Errors found deleting existing invoice.");
        }

        return $invoiceId;
    }

    /**
     * Calcula y actualiza subtotal, total de impuestos y total de la factura.
     *
     * @param array $data Array of items, where each item contains a 'Total charge' key representing its charge amount.
     * @param int $idTax The ID of the tax to be applied.
     * @param float $subTotal Reference variable to store the calculated subtotal of all items.
     * @param float $taxTotal Reference variable to store the calculated tax amount based on the subtotal.
     * @param float $invoiceTotal Reference variable to store the total invoice amount, which is the sum of the subtotal and tax total.
     * @return void
     */
    private function getInvoiceSummary($data, $idTax, &$subTotal, &$taxTotal, &$invoiceTotal)
    {
        $subTotal = 0;
        $taxTotal = 0;
        $invoiceTotal = 0;

        foreach ($data as $item) {
            $subTotal += $item['Total charge'];
        }

        $taxTotal = $this->getTaxAmount($idTax, $subTotal);
        $invoiceTotal = $subTotal + $taxTotal;
    }
}