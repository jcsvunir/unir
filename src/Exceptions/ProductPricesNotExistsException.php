<?php

namespace Exceptions;

class ProductPricesNotExistsException extends \Exception
{
    public function __construct($productName )
    {
        parent::__construct("Prices not found for product#$productName", 0);
    }

}