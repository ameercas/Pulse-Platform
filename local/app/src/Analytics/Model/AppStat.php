<?php
namespace Analytics\Model;

use Eloquent, DB;

Class AppStat extends Eloquent
{

	protected $table = 'app_stats';

	public $timestamps = false;

	public static function boot()
	{
	  static::creating(function($model)
	  {
	      $model->created_at = $model->freshTimestamp();
	  });
	}

	public function app()
	{
		return $this->belongsTo('Mobile\Model\App');
	}

	public function appPage()
	{
		return $this->belongsTo('Mobile\Model\AppPage');
	}

}
