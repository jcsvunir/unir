<?php


namespace Exceptions;


use Throwable;

class UndefinedCountryNameException extends \Exception
{
    public function __construct($networkCode )
    {
        parent::__construct("Undefined country name for MCC/MNC#$networkCode", 0);
    }

}