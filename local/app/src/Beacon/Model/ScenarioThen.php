<?php
namespace Beacon\Model;

use Eloquent, DB;

Class ScenarioThen extends Eloquent
{
    protected $table='scenario_then';

	// Disabling Auto Timestamps
    public $timestamps = false;

    public function scenarios()
    {
        return $this->belongsToMany('Beacon\Model\Scenario', 'scenarios');
    }

}