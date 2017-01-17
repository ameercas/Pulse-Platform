<?php
namespace Beacon\Model;

use Eloquent, DB;

Class ScenarioIf extends Eloquent
{
    protected $table='scenario_if';

	// Disabling Auto Timestamps
    public $timestamps = false;

    public function scenarios()
    {
        return $this->belongsToMany('Beacon\Model\Scenario', 'scenarios');
    }

}