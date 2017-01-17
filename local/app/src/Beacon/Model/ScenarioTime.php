<?php
namespace Beacon\Model;

use Eloquent, DB;

Class ScenarioTime extends Eloquent
{
    protected $table='scenario_time';

	// Disabling Auto Timestamps
    public $timestamps = false;

    public function scenarios()
    {
        return $this->belongsToMany('Beacon\Model\Scenario', 'scenarios');
    }

}