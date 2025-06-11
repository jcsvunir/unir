<?php

namespace Billing\OMV1;

use Core\Timer;
use Exceptions\UndefinedCountryNameException;
use Exceptions\UndefinedPLMNNameException;
use Exceptions\UnitChargeNotFoundException;
use Illuminate\Database\Capsule\Manager as DB;
use Models\OMV1\Customer;
use Models\OMV1\Invoice;
use Models\OMV1\InvoiceDetail;
use Models\OMV1\OMV1CustomerPrice;
use Models\OMV1\OMV1NetworkOperator;
use Models\OMV1\Tax;

trait DataRoutine
{


    /**
     * Return Country name of a concatenated mcc + mnc
     * @param string $networkCode
     * @return string
     * @throws UndefinedCountryNameException
     */
    public function getCountryName(string $networkCode):string{
        $network = OMV1NetworkOperator::where('mcc','=', mb_substr($networkCode, 0,3))->where('mnc', '=', mb_substr($networkCode, 3,3))->get();
        if ($network->isNotEmpty()){
            $result = $network->first()->country;
        }else{
            throw new UndefinedCountryNameException($networkCode);
        }

        return $result;

    }

    public function getNextBillInternalNumber(){

        $functionData = DB::select("SELECT GETINTERNALNUMBER() AS INTERNAL_NUMBER");
        $nextInternalNumber = array_shift($functionData)->INTERNAL_NUMBER;
        return $nextInternalNumber[0];

    }

    /**
     * Return Customer name of a given id
     * @param string $idCustomer
     * @return string
     */
    public function getCustomerName(string $idCustomer):string{
        $customer = Customer::where('id','=', $idCustomer)->first();
        return $customer->name;
    }

    /**
     * Return Customer internal number of a given id
     * @param string $idCustomer
     * @return string
     */
    public function getCustomerAccountID(string $idCustomer):string{
        $customer = Customer::where('id','=', $idCustomer)->first();
        return $customer->internal_number;
    }

    /**
     * @param string $networkCode
     * @return string
     * @throws UndefinedPLMNNameException
     */
    public function getNetworkOperator(string $networkCode):string{

        $network = OMV1NetworkOperator::where('mcc','=', mb_substr($networkCode, 0,3))->where('mnc', '=', mb_substr($networkCode, 3,3))->get();
        if ($network->isNotEmpty()){
            $result = $network->first()->operator;
        }else{
            throw new UndefinedPLMNNameException($networkCode);
        }

        return $result;
    }

    /**
     * @param string $idCustomer
     * @param string $networkCode
     * @return float
     * @throws UnitChargeNotFoundException
     */
    public function getCurrentUnitCharge(string $idCustomer, string $networkCode):float {

        $price = OMV1CustomerPrice::where('id_customer','=', $idCustomer)
                                    ->where('mcc','=', mb_substr($networkCode, 0,3))
                                    ->where('mnc', '=', mb_substr($networkCode, 3,3))
                                    ->where('start_date', '<=', (new \DateTime())->format('Y-m-d\TH:i:s.u'))
                                    ->whereNull('end_date')->get();
        //print_r($idCustomer . " " . $networkCode . " " . $price);
        if ($price->isNotEmpty()) {
            $result = $price->first()->unit_price_data_mb;
        }else{
            throw new UnitChargeNotFoundException($networkCode);
        }

        return $result;
    }

    public function getPeriodUnitCharge(string $idCustomer, string $networkCode, string $period):float {

        $firstDayOfPeriod = $period . '-01';
        $lastDayOfPeriod = $period . '-' . (new Timer())->getLastDayOfDate($firstDayOfPeriod);
        $price = OMV1CustomerPrice::where('id_customer','=', $idCustomer)
            ->where('mcc','=', mb_substr($networkCode, 0,3))
            ->where('mnc', '=', mb_substr($networkCode, 3,3))
            ->where('start_date', '<=', $firstDayOfPeriod)
            ->where('end_date', '>=', $lastDayOfPeriod)->get();
        //print_r($idCustomer . " " . $networkCode . " " . $price);
        if ($price->isNotEmpty()) {
            $result = $price->first()->unit_price_data_mb;
        }else{

            $result = $this->getCurrentUnitCharge($idCustomer, $networkCode);

        }

        //echo "\nPrice for $networkCode: $result";
        return $result;
    }

    public function getChildCustomers(string $idCustomer):array {
        $childs = Customer::where('id_parent', '=', $idCustomer)->where('mno','=',3)->get();

        if ($childs->isNotEmpty()) {
            $result = array();
            foreach ($childs as $child){
                $result[] = $child->id;
            }

        }else{
            $result = array();
        }

        //print_r($result);
        return $result;

    }


    /**
     * @param $idInvoice
     * @return mixed
     */
    public function removeInvoice($idInvoice){
        // Remove Invoice details
        InvoiceDetail::where('id_invoice','=', $idInvoice)->delete();

        return Invoice::where('id_invoice','=', $idInvoice)->delete();
    }

    public function getDefaultVATId(){
        return Tax::where('rate', '>',0)->first()->id;
    }

    /**
     * @param $idCustomer
     * @return string|null
     */
    public function getCustomerVATId($idCustomer){
        $result = null;
        $customerRecords = Customer::where('id','=',$idCustomer)->get();
        if ($customerRecords->isNotEmpty()){
            $customer = $customerRecords->first();
            $defaultVat = $customer->default_vat;
            $result = (is_null($defaultVat) || $defaultVat == "") ? $this->getDefaultVATId(): $defaultVat;
        }else{
            $result = $this->getDefaultVATId();
        }

        //echo "\n" . __FUNCTION__ . " Result:$result\n";
        return $result;
    }

    public function getTaxAmount($idTax, $subTotal){
        return (Tax::where('id', '=',$idTax)->first()->rate / 100) * $subTotal;

    }
}