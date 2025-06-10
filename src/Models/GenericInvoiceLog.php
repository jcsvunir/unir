<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
class GenericInvoiceLog extends Model
{
    protected $table = 'invoice_logs';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
}