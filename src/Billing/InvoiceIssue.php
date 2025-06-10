<?php


namespace Billing;


class InvoiceIssue extends Issue
{


    private ?string $period = null;
    private ?string $idCustomer = null;


    public function __construct($type, $severity, $description, $idCustomer, $period)
    {
        parent::__construct($type, $severity, $description);
        $this->idCustomer = $idCustomer;
        $this->period = $period;
    }


    public function getIdCustomer(): string
    {
        return $this->idCustomer;
    }

    public function getPeriod(): string
    {
        return $this->period;
    }
}