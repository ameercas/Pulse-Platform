<?php
namespace Beacon\Model;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Eloquent;

class ScenarioBoard extends Eloquent implements StaplerableInterface
{
    use EloquentTrait;

    protected $table = 'scenario_boards';


    /**
     * Validation rules
     */
 
    public static $rules = array(
        'user_id'          => 'required|integer',
        'name'             => 'required|between:1,64'
    );

    /**
     * Laravel-Stapler
     */

    protected $fillable = ['photo'];

    public function __construct(array $attributes = array()) {
        $this->hasAttachedFile('photo', [
            'styles' => [
                'thumbnail' => '420x315#',
                'small' => '140x105#',
                'tiny' => '84x63#'
            ]
        ]);

        parent::__construct($attributes);
       // parent::boot();

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

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function apps()
    {
        return $this->belongsToMany('Mobile\Model\App', 'app_scenario_board', 'scenario_board_id', 'app_id');
    }

    public function sites()
    {
        return $this->belongsToMany('Web\Model\Site', 'site_scenario_board', 'scenario_board_id', 'site_id');
    }

    public function scenarios()
    {
        return $this->hasMany('Beacon\Model\Scenario');
    }

}