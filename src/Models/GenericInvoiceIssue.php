<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class GenericInvoiceIssue extends Model
{
    protected $table = 'invoice_issues';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
}