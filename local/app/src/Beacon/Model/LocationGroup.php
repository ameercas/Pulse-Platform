<?php
namespace Beacon\Model;

use Eloquent, DB;

Class LocationGroup extends Eloquent
{
    protected $table='location_groups';

	// Disabling Auto Timestamps
    public $timestamps = false;

	public function getAttribute($key)
	{
		$value = parent::getAttribute($key);
		if($key == 'settings' && $value)
        {
		    $value = json_decode($value);
		}
		return $value;
	}

	public function setAttribute($key, $value)
	{
		if($key == 'settings' && $value)
        {
		    $value = json_encode($value);
		}
		parent::setAttribute($key, $value);
	}

	public function toArray()
	{
		$attributes = parent::toArray();
		if(isset($attributes['settings']))
        {
			$attributes['settings'] = json_decode($attributes['settings']);
		}
		return $attributes;
	}

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function geofences()
    {
        return $this->hasMany('Beacon\Model\Geofence');
    }

    public function beacons()
    {
        return $this->hasMany('Beacon\Model\Beacon');
    }

}