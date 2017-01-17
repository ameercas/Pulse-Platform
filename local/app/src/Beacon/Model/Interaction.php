<?php
namespace Beacon\Model;

use Eloquent, DB;

Class Interaction extends Eloquent
{

  protected $table='interactions';

  public function setUpdatedAtAttribute($value)
  {
    // Do nothing.
  }

  public function user()
  {
    return $this->belongsTo('User');
  }

  public function app()
  {
    return $this->hasOne('Mobile\Model\App');
  }

  public function site()
  {
    return $this->hasOne('Web\Model\Site');
  }
}