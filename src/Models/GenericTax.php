<?php

namespace Models;

class GenericTax extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'taxes';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
}