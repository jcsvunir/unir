<?php

namespace Exceptions;

class ProductNotExistsException extends \Exception
{
    public function __construct($productName )
    {
        parent::__construct("Product details not found for product#$productName", 0);
    }

}