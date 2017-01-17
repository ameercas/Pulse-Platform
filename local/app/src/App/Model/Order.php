<?php
namespace App\Model;

use Eloquent;

Class Order extends Eloquent
{

    protected $table='orders';

    public function getDates()
    {
      return array('created_at', 'updated_at', 'invoice_date', 'invoice_datetime', 'expires');
    }

    public function getInvoiceDateAttribute($value)
    {
        return $value;
    }

    public function getExpiresAttribute($value)
    {
        return $value;
    }

    public function users()
    {
        return $this->belongsTo('User');
    }

    public function resellers()
    {
        return $this->belongsTo('App\Model\Reseller');
    }

}