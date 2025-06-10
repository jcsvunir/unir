<?php


namespace Exceptions;


use Throwable;

class InvoiceIssuesException extends \Exception
{

    public function __construct()
    {
        parent::__construct("Invoice has not fixed issues.", 0, null);
    }
}