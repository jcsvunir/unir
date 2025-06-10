<?php


namespace Exceptions;


use Throwable;

class UnitChargeNotFoundException extends \Exception
{

    public function __construct($message = "")
    {
        parent::__construct("Unit charge record for MCC/MNC#$message, does not exists.", 0);
    }
}