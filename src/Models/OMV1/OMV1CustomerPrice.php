<?php

namespace Models\OMV1;
use Models\GenericModel;
class OMV1CustomerPrice extends GenericModel
{
    protected $table = 'omv1_customer_prices';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
}