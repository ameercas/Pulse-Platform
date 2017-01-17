<?php
namespace Beacon\Controller;

use View, Auth, Input, Cache;

/*
|--------------------------------------------------------------------------
| Beacon controller
|--------------------------------------------------------------------------
|
| Beacon related logic
|
*/

class BeaconController extends \BaseController {

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
   * Show all beacons partial
   */
  public function getBeacons()
  {
    $beacons = \Beacon\Model\Beacon::where('user_id', '=', $this->parent_user_id)->orderBy('name', 'asc');

    return View::make('app.beacons.beacons', array(
      'beacons' => $beacons
    ));
  }

  /**
   * Show beacon partial
   */
  public function getBeacon()
  {
    $sl = \Request::input('sl', '');

    // Get all beacon groups / locations
    $location_groups = \Beacon\Model\LocationGroup::where('user_id', '=', $this->parent_user_id)->orderBy('name', 'asc')->get();

    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
      $beacon = \Beacon\Model\Beacon::where('id', $qs['beacon_id'])->where('user_id', '=', $this->parent_user_id)->first();
/*
      if ($beacon->lat != NULL)
      {
        $geo = NULL;
        $beacon_location = $beacon->lat . ',' . $beacon->lng;
      }
      else
      {
        $geo = \App\Core\Geo::ip2address();
        $beacon_location =  $geo['latitude'] . ',' . $geo['longitude'];
      }
*/
      return View::make('app.beacons.beacon-edit', array(
        'sl' => $sl,
        'beacon' => $beacon,
        /*'beacon_location' => $beacon_location,*/
        'location_groups' => $location_groups,
        /*'geo' => $geo*/
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

      $plan_max_beacons = (isset($plan_settings->max_beacons)) ? $plan_settings->max_beacons : 1;

      $beacons = \Beacon\Model\Beacon::where('user_id', '=', $this->parent_user_id)->count();

      $beacon_limit = ($plan_max_beacons != 0 && $beacons >= (int) $plan_max_beacons) ? true : false;

      if($beacon_limit)
      {
        return View::make('app.auth.upgrade');
        die();
      }

      $geo = \App\Core\Geo::ip2address();

      return View::make('app.beacons.beacon-new', array(
        'location_groups' => $location_groups,
        'geo' => $geo
      ));
    }
  }

  /**
   * Show import beacons modal
   */
  public function getBeaconImportModal()
  {
    return View::make('app.beacons.modal.beacon-import');
  }

  /**
   * Save (new) beacon
   */
  public function postSave()
  {
    $sl = \Request::get('sl', NULL);
    $group = \Request::get('group', NULL);
    $location = \Request::get('location', NULL);
    $name = \Request::get('name');
    $uuid = \Request::get('uuid');
    $major = \Request::get('major', NULL);
    $minor = \Request::get('minor', NULL);

    if($sl != NULL)
    {
      $qs = \App\Core\Secure::string2array($sl);
        $beacon = \Beacon\Model\Beacon::where('id', $qs['beacon_id'])->where('user_id', '=', $this->parent_user_id)->first();
    }
    else
    {
      $beacon = new \Beacon\Model\Beacon;
    }

    $beacon->user_id = $this->parent_user_id;
    $beacon->name = $name;
    $beacon->uuid = $uuid;
    $beacon->major = ($major == '') ? NULL : $major;
    $beacon->minor = ($minor == '') ? NULL : $minor;
/*
    if ($location != NULL)
    {
      $loc = explode(',', $location);
      if (isset($loc[0])) $beacon->lat = $loc[0];
      if (isset($loc[1])) $beacon->lng = $loc[1];
    }
*/
    if($group != NULL)
    {
      $group = json_decode($group);

      if($group->id > 0)
      {
        // Group already exists, just set group_id
        $beacon->location_group_id = $group->id;
      }
      else
      {
        // Group doesn't exist yet, add it first
        $location_group = new \Beacon\Model\LocationGroup;
        $location_group->user_id = $this->parent_user_id;
        $location_group->name = $group->text;
        if($location_group->save())
        {
          $beacon->location_group_id = $location_group->id;
        }
      }
    }

    if($beacon->save())
    {
      $response = array(
        'result' => 'success', 
        'result_msg' => trans('global.beacon_added')
      );
    }
    else
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $beacon->errors()->first()
      );
    }

    return \Response::json($response);
  }

  /**
   * Delete beacon(s)
   */
  public function postDelete()
  {
    $sl = \Request::input('sl', '');

    if(\Auth::check() && $sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);

      $beacon = \Beacon\Model\Beacon::where('id', '=',  $qs['beacon_id'])->where('user_id', '=',  $this->parent_user_id)->delete();
    }
    elseif (\Auth::check())
    {
      foreach(\Request::get('ids', array()) as $id)
      {
        $affected = \Beacon\Model\Beacon::where('id', '=', $id)->where('user_id', '=',  $this->parent_user_id)->delete();
      }
    }

    return \Response::json(array('result' => 'success'));
  }

  /**
   * Switch beacon(s)
   */
  public function postSwitch()
  {
    if(\Auth::check())
    {
      foreach(\Request::get('ids', array()) as $id)
      {
        $current = \Beacon\Model\Beacon::where('id', '=', $id)->first();
        $switch = ($current->active == 1) ? 0 : 1;
        $affected = \Beacon\Model\Beacon::where('id', '=', $id)->where('user_id', '=',  $this->parent_user_id)->update(array('active' => $switch));
      }
    }

    return \Response::json(array('result' => 'success'));
  }

  /**
   * Get beacon list data
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

    $aColumn = array('bg.name', 'beacons.name', 'beacons.uuid', 'beacons.major', 'beacons.minor', 'beacons.active');

    if($q != '')
    {
      $count = \Beacon\Model\Beacon::leftJoin('location_groups as bg', 'beacons.location_group_id', '=', 'bg.id')
        ->orderBy($aColumn[$order_by], $order)
        ->select(array('beacons.id', 'beacons.name', 'beacons.uuid', 'beacons.major', 'beacons.minor', 'beacons.active', 'bg.name as group_name'))
        ->where(function ($query) {
          $query->where('beacons.user_id', '=', $this->parent_user_id);
        })
        ->where(function ($query) use($q) {
          $query->orWhere('beacons.name', 'like', '%' . $q . '%');
          $query->orWhere('beacons.uuid', 'like', '%' . $q . '%');
          $query->orWhere('beacons.major', 'like', '%' . $q . '%');
          $query->orWhere('beacons.minor', 'like', '%' . $q . '%');
          $query->orWhere('bg.name', 'like', '%' . $q . '%');
        })
        ->count();

      $oData = \Beacon\Model\Beacon::leftJoin('location_groups as bg', 'beacons.location_group_id', '=', 'bg.id')
        ->orderBy($aColumn[$order_by], $order)
        ->select(array('beacons.id', 'beacons.name', 'beacons.uuid', 'beacons.major', 'beacons.minor', 'beacons.active', 'bg.name as group_name'))
        ->where(function ($query) {
          $query->where('beacons.user_id', '=', $this->parent_user_id);
        })
        ->where(function ($query) use($q) {
          $query->orWhere('beacons.name', 'like', '%' . $q . '%');
          $query->orWhere('beacons.uuid', 'like', '%' . $q . '%');
          $query->orWhere('beacons.major', 'like', '%' . $q . '%');
          $query->orWhere('beacons.minor', 'like', '%' . $q . '%');
          $query->orWhere('bg.name', 'like', '%' . $q . '%');
        })
        ->take($length)->skip($start)->get();
    }
    else
    {
      $count = \Beacon\Model\Beacon::where('beacons.user_id', '=', $this->parent_user_id)->count();

      $oData = \Beacon\Model\Beacon::where('beacons.user_id', '=', $this->parent_user_id)
        ->leftJoin('location_groups as bg', 'beacons.location_group_id', '=', 'bg.id')
        ->select(array('beacons.id', 'beacons.name', 'beacons.uuid', 'beacons.major', 'beacons.minor', 'beacons.active', 'bg.name as group_name'))
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
        'uuid' => $row->uuid,
        'major' => ($row->major === NULL) ? '-' : $row->major,
        'minor' => ($row->minor === NULL) ? '-' : $row->minor,
        'active' => $row->active,
        'sl' => \App\Core\Secure::array2string(array('beacon_id' => $row->id))
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