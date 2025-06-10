<?php

namespace Exceptions;

class ProductPoolNotExistsException extends \Exception
{

    public function __construct($idProduct, $idCustomer){
        parent::__construct("Pool definition not found for product: $idProduct and customer: $idCustomer", 0);
    }
}