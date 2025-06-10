<?php

namespace Models\OMV1;

use Models\GenericModel;
class OMV1NetworkOperator extends GenericModel
{
    protected $table = 'omv1_network_operators';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
}