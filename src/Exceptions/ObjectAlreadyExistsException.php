<?php


namespace Exceptions;


use Throwable;

class ObjectAlreadyExistsException extends \Exception
{
    public function __construct($objectPath, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Already exists an object on path: $objectPath.", $code, $previous);
    }
}