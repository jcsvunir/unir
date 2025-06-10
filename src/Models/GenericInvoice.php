<?php

namespace Models;

class GenericInvoice extends \Illuminate\Database\Eloquent\Model
{

    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
}