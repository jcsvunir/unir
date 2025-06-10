<?php


namespace Billing;


class InvoiceIssueList implements \Iterator
{
    private $position = 0;
    private $totalErrors = 0;
    private $totalWarnings = 0;
    private $totalInfos = 0;
    private $array = array();
    private $errorsIndexes = array();
    private $infosIndexes = array();
    private $warningsIndexes = array();

    public function __construct() {
        $this->position = 0;
    }

    public function rewind() {

        $this->position = 0;
    }

    public function current() {

        return $this->array[$this->position];
    }

    public function key() {

        return $this->position;
    }

    public function next() {

        ++$this->position;
    }

    public function valid() {

        return isset($this->array[$this->position]);
    }


    public function hasIssues() {
        return count($this->array);
    }

    public function add(InvoiceIssue $invoiceIssue){


        $indexLabel = "";
        switch($invoiceIssue->getSeverity()){
            case SeverityEnumerator::INFO:
                $indexLabel = "infosIndexes";
                $this->totalInfos++;
                break;
            case SeverityEnumerator::ERROR:
                $indexLabel = "errorsIndexes";
                $this->totalErrors++;
                break;
            case SeverityEnumerator::WARNING;
                $indexLabel = "warningsIndexes";
                $this->totalWarnings++;
                break;
        }
        $this->{$indexLabel}[] = count($this->array);
        //print_r($this->{$indexLabel} );
        $this->array[] = $invoiceIssue;

    }

    public function count(){
        return count($this->array);
    }

    public function getErrors(){
        return $this->getIssueItems("errorsIndexes");
    }

    public function getInfos(){
        return $this->getIssueItems("infosIndexes");
    }

    public function getWarnings(){
        return $this->getIssueItems("warningsIndexes");
    }

    /**
     * @return array
     */
    public function getIssuesArray(){
        return $this->array;
    }
    private function getIssueItems($label){
        $result = array();
        //print_r($this->array);
        //print_r($this->{$label});
        foreach($this->{$label} as $key => $value){
            $result[] = $this->array[$value];
            //print_r($this->array[$value]);
        }
        return $result;
    }

    public function getTotalErrors(){
        return $this->totalErrors;
    }

    public function getTotalInfos()
    {
        return $this->totalInfos;
    }


    public function getTotalWarnings(){
        return $this->totalWarnings;
    }

}