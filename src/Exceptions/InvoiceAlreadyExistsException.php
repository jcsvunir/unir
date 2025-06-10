<?php


namespace Exceptions;


use Throwable;

class InvoiceAlreadyExistsException extends \Exception
{
    public function __construct($invoiceId, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Already exists an invoice with same billing cycle period. ID#$invoiceId.", $code, $previous);
    }

}