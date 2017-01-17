<?php
namespace Beacon\Controller;

use View, Auth, Input, Cache;

/*
|--------------------------------------------------------------------------
| Geofence controller
|--------------------------------------------------------------------------
|
| Geofence related logic
|
*/

class GeofenceController extends \BaseController {

    /**
	 * Construct
     */
    public function __construct()
    {
		if(Auth::check())
		{
			$this->parent_user_id = (Auth::user()->parent_id == NULL) ? Auth::user()->id : Auth::user()->parent_id;
		}
		else
		{
			$this->parent_user_id = NULL;
		}
    }

    /**
     * Show all geofences partial
     */
    public function getGeofences()
    {
		$geofences = \Beacon\Model\Geofence::where('user_id', '=', $this->parent_user_id)->orderBy('name', 'asc');

        return View::make('app.beacons.geofences', array(
			'geofences' => $geofences
		));
    }

    /**
     * Show geofence partial
     */
    public function getGeofence()
    {
		$sl = \Request::input('sl', '');

		// Get all geofence groups / locations
		$location_groups = \Beacon\Model\LocationGroup::where('user_id', '=', $this->parent_user_id)->orderBy('name', 'asc')->get();

		if($sl != '')
		{
			$qs = \App\Core\Secure::string2array($sl);
       		$geofence = \Beacon\Model\Geofence::where('id', $qs['geofence_id'])->where('user_id', '=', $this->parent_user_id)->first();

			if ($geofence->lat != NULL)
			{
				$lat = $geofence->lat;
				$lng = $geofence->lng;
				$geo = NULL;
				$geofence_location = $lat . ',' . $lng;
			}
			else
			{
				$lat = $geo['latitude'];
				$lng = $geo['longitude'];
				$geo = \App\Core\Geo::ip2address();
				$geofence_location =  $geo['latitude'] . ',' . $geo['longitude'];
			}

			return View::make('app.beacons.geofence-edit', array(
				'sl' => $sl,
				'geofence' => $geofence,
				'geofence_location' => $geofence_location,
				'location_groups' => $location_groups,
				'lat' => $lat,
				'lng' => $lng,
				'geo' => $geo
			));
		}
		else
		{
      if (\Auth::user()->parent_id != '')
      {
        $parent_user = \User::where('id', '=', \Auth::user()->parent_id)->first();
        $plan_settings = $parent_user->plan->settings;
      }
      else
      {
        $plan_settings = \Auth::user()->plan->settings;
      }
  
      $plan_settings = json_decode($plan_settings);
  
      $plan_max_geofences = (isset($plan_settings->max_geofences)) ? $plan_settings->max_geofences : 1;
  
      $geofences = \Beacon\Model\Geofence::where('user_id', '=', $this->parent_user_id)->count();
  
      $geofence_limit = ($plan_max_geofences != 0 && $geofences >= (int) $plan_max_geofences) ? true : false;
  
      if($geofence_limit)
      {
        return View::make('app.auth.upgrade');
        die();
      }

			$geo = \App\Core\Geo::ip2address();
			$lat = $geo['latitude'];
			$lng = $geo['longitude'];

			return View::make('app.beacons.geofence-new', array(
				'location_groups' => $location_groups,
				'lat' => $lat,
				'lng' => $lng,
				'geo' => $geo
			));
		}
    }

    /**
     * Save (new) geofence
     */
    public function postSave()
    {
        $sl = \Request::get('sl', NULL);
        $group = \Request::get('group', NULL);
        $location = \Request::get('location', NULL);
        $radius = \Request::get('radius', 25);
        $name = \Request::get('name');

		if($sl != NULL)
		{
			$qs = \App\Core\Secure::string2array($sl);
      		$geofence = \Beacon\Model\Geofence::where('id', $qs['geofence_id'])->where('user_id', '=', $this->parent_user_id)->first();
		}
		else
		{
	        $geofence = new \Beacon\Model\Geofence;
		}

        $geofence->user_id = $this->parent_user_id;
        $geofence->name = $name;
        $geofence->radius = $radius;

		if ($location != NULL)
		{
			$loc = explode(',', $location);
	        if (isset($loc[0])) $geofence->lat = $loc[0];
	        if (isset($loc[1])) $geofence->lng = $loc[1];
		}

        if($group != NULL)
        {
            $group = json_decode($group);
            if($group->id > 0)
            {
                // Group already exists, just set group_id
                $geofence->location_group_id = $group->id;
            }
            else
            {
                // Group doesn't exist yet, add it first
                $location_group = new \Beacon\Model\LocationGroup;
                $location_group->user_id = $this->parent_user_id;
                $location_group->name = $group->text;
                if($location_group->save())
                {
                    $geofence->location_group_id = $location_group->id;
                }
            }
        }

        if($geofence->save())
        {
            $response = array(
                'result' => 'success', 
                'result_msg' => trans('global.geofence_saved')
            );
        }
        else
        {
            $response = array(
                'result' => 'error', 
                'result_msg' => $geofence->errors()->first()
            );
        }

		return \Response::json($response);
    }

    /**
     * Delete geofence(s)
     */
    public function postDelete()
    {
		$sl = \Request::input('sl', '');

        if(\Auth::check() && $sl != '')
        {
            $qs = \App\Core\Secure::string2array($sl);

            $geofence = \Beacon\Model\Geofence::where('id', '=',  $qs['geofence_id'])->where('user_id', '=',  $this->parent_user_id)->delete();
        }
		elseif (\Auth::check())
        {
			foreach(\Request::get('ids', array()) as $id)
			{
				$affected = \Beacon\Model\Geofence::where('id', '=', $id)->where('user_id', '=',  $this->parent_user_id)->delete();
			}
        }

		return \Response::json(array('result' => 'success'));
    }

    /**
     * Switch geofence(s)
     */
    public function postSwitch()
    {
        if(\Auth::check())
        {
			foreach(\Request::get('ids', array()) as $id)
			{
                $current = \Beacon\Model\Geofence::where('id', '=', $id)->first();
                $switch = ($current->active == 1) ? 0 : 1;
                $affected = \Beacon\Model\Geofence::where('id', '=', $id)->where('user_id', '=',  $this->parent_user_id)->update(array('active' => $switch));
			}
        }

		return \Response::json(array('result' => 'success'));
    }

    /**
     * Get geofence list data
     */
    public function getData()
    {
		$order_by = Input::get('order.0.column', 0);
		$order = Input::get('order.0.dir', 'asc');
		$search = Input::get('search.regex', '');
		$q = Input::get('search.value', '');
		$start = Input::get('start', 0);
		$draw = Input::get('draw', 1);
		$length = Input::get('length', 10);
		$data = array();

		$aColumn = array('bg.name', 'geofences.name', 'geofences.lat', 'geofences.lng', 'geofences.radius', 'geofences.active');

		if($q != '')
		{
			$count = \Beacon\Model\Geofence::leftJoin('location_groups as bg', 'geofences.location_group_id', '=', 'bg.id')
                ->orderBy($aColumn[$order_by], $order)
                ->select(array('geofences.id', 'geofences.name', 'geofences.lat', 'geofences.lng', 'geofences.radius', 'geofences.active', 'bg.name as group_name'))
				->where(function ($query) {
					$query->where('geofences.user_id', '=', $this->parent_user_id);
				})
				->where(function ($query) use($q) {
					$query->orWhere('geofences.name', 'like', '%' . $q . '%');
					$query->orWhere('geofences.lat', 'like', '%' . $q . '%');
					$query->orWhere('geofences.lng', 'like', '%' . $q . '%');
					$query->orWhere('geofences.radius', 'like', '%' . $q . '%');
					$query->orWhere('bg.name', 'like', '%' . $q . '%');
				})
				->count();

			$oData = \Beacon\Model\Geofence::leftJoin('location_groups as bg', 'geofences.location_group_id', '=', 'bg.id')
                ->orderBy($aColumn[$order_by], $order)
                ->select(array('geofences.id', 'geofences.name', 'geofences.lat', 'geofences.lng', 'geofences.radius', 'geofences.active', 'bg.name as group_name'))
				->where(function ($query) {
					$query->where('geofences.user_id', '=', $this->parent_user_id);
				})
				->where(function ($query) use($q) {
					$query->orWhere('geofences.name', 'like', '%' . $q . '%');
					$query->orWhere('geofences.lat', 'like', '%' . $q . '%');
					$query->orWhere('geofences.lng', 'like', '%' . $q . '%');
					$query->orWhere('geofences.radius', 'like', '%' . $q . '%');
					$query->orWhere('bg.name', 'like', '%' . $q . '%');
				})
				->take($length)->skip($start)->get();
		}
		else
		{
			$count = \Beacon\Model\Geofence::where('geofences.user_id', '=', $this->parent_user_id)->count();

			$oData = \Beacon\Model\Geofence::where('geofences.user_id', '=', $this->parent_user_id)
                ->leftJoin('location_groups as bg', 'geofences.location_group_id', '=', 'bg.id')
                ->select(array('geofences.id', 'geofences.name', 'geofences.lat', 'geofences.lng', 'geofences.radius', 'geofences.active', 'bg.name as group_name'))
                ->orderBy($aColumn[$order_by], $order)
                ->take($length)
                ->skip($start)
                ->get();
		}

		if($length == -1) $length = $count;

		$recordsTotal = $count;
		$recordsFiltered = $count;

		foreach($oData as $row)
		{
			$data[] = array(
				'DT_RowId' => 'row_' . $row->id,
				'location_group_id' => $row->group_name,
				'name' => $row->name,
				'lat' => $row->lat,
				'lng' => $row->lng,
				'radius' => $row->radius,
				'active' => $row->active,
				'sl' => \App\Core\Secure::array2string(array('geofence_id' => $row->id))
				/*,
				'created_at' => $row->created_at->timezone(Auth::user()->timezone)->format(trans('global.dateformat_full'))*/
			);
		}

		$response = array(
			'draw' => $draw,
			'recordsTotal' => $recordsTotal,
			'recordsFiltered' => $recordsFiltered,
			'data' => $data
		);

		echo json_encode($response);
    }
}