<?php

namespace Models;

class GenericInvoiceDetail extends \Illuminate\Database\Eloquent\Model
{

    protected $table = 'invoice_details';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

}