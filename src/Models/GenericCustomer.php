<?php

namespace Models;

class GenericCustomer extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
}