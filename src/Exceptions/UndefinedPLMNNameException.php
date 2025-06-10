<?php


namespace Exceptions;


class UndefinedPLMNNameException extends \Exception
{
    public function __construct($networkCode )
    {
        parent::__construct("Undefined PLMN name for MCC/MNC#$networkCode", 0);
    }
}