<?php
namespace Beacon\Model;

use Watson\Validating\ValidatingTrait;
use Eloquent, DB;

Class Scenario extends Eloquent
{
	use ValidatingTrait;

    protected $table = 'scenarios';

    /**
     * Validation rules
     */
 
    public static $rules = array(
    );

    public function __construct(array $attributes = array()) {

        parent::__construct($attributes);

        static::creating(function($item)
        {
			$item->created_by = \Auth::user()->id;
			$item->updated_by = \Auth::user()->id;
        });

        static::updating(function($item)
        {
			$item->updated_by = \Auth::user()->id;
        });
    }

    public function scenarioBoard()
    {
        return $this->belongsTo('Beacon\Model\ScenarioBoard');
    }

    public function geofences()
    {
        return $this->belongsToMany('Beacon\Model\Geofence', 'geofence_scenario', 'scenario_id', 'geofence_id');
    }

    public function beacons()
    {
        return $this->belongsToMany('Beacon\Model\Beacon', 'beacon_scenario', 'scenario_id', 'beacon_id');
    }

    public function scenarioIf()
    {
        return $this->hasOne('Beacon\Model\ScenarioIf');
    }

    public function scenarioThen()
    {
        return $this->hasOne('Beacon\Model\ScenarioThen');
    }

    public function scenarioDay()
    {
        return $this->hasOne('Beacon\Model\ScenarioDay');
    }

    public function scenarioTime()
    {
        return $this->hasOne('Beacon\Model\ScenarioTime');
    }

}