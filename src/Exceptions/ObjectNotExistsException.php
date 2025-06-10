<?php


namespace Exceptions;


use Throwable;

class ObjectNotExistsException extends \Exception
{

    public function __construct($message)
    {
        parent::__construct("Object: $message, does not exists.", 0, null);
    }
}