<?php
namespace Beacon\Model;

use Eloquent, DB;

Class ScenarioDay extends Eloquent
{
    protected $table='scenario_day';

	// Disabling Auto Timestamps
    public $timestamps = false;

    public function scenarios()
    {
        return $this->belongsToMany('Beacon\Model\Scenario', 'scenarios');
    }

}