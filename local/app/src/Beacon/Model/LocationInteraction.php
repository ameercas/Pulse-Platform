<?php
namespace Beacon\Model;

use Eloquent, DB;

Class LocationInteraction extends Eloquent
{

    protected $table='location_interactions';

	public function setUpdatedAtAttribute($value)
	{
		// Do nothing.
	}

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function scenario()
    {
        return $this->hasOne('Beacon\Model\Scenario');
    }

    public function beacon()
    {
        return $this->hasOne('Beacon\Model\Beacon');
    }
}