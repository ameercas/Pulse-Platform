<?php
namespace Beacon\Model;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Watson\Validating\ValidatingTrait;
use Eloquent, DB;

Class Beacon extends Eloquent implements StaplerableInterface
{
    use EloquentTrait;
	use ValidatingTrait;

    protected $table='beacons';

    /**
     * Validation rules
     */
 
    public static $rules = array(
        'user_id'          => 'required|integer',
        'name'             => 'required|between:1,64',
        'uuid'             => 'required|between:1,42'
    );

    /**
     * Laravel-Stapler
     */

    protected $fillable = ['photo'];

    public function __construct(array $attributes = array()) {
        $this->hasAttachedFile('photo', [
            'styles' => [
                'large' => '800x800',
                'small' => '128x128#'
            ]
        ]);

        parent::__construct($attributes);

        static::creating(function($item)
        {
			//$item->created_by = \Auth::user()->id;
			//$item->updated_by = \Auth::user()->id;
        });

        static::updating(function($item)
        {
			//$item->updated_by = \Auth::user()->id;
        });
    }

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function scenario()
    {
        return $this->belongsToMany('Beacon\Model\Scenario', 'beacon_scenario', 'scenario_id', 'beacon_id');
    }

    public function locationGroup()
    {
        return $this->belongsTo('Beacon\Model\LocationGroup', 'location_group_id');
    }

    public function scenarios()
    {
        return $this->belongsToMany('Beacon\Model\Scenario', 'beacon_scenario');
    }

}