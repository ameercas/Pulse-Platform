<?php
namespace Analytics\Controller;

use View, Auth, Input, Cache;

/*
|--------------------------------------------------------------------------
| Interactions Analytics
|--------------------------------------------------------------------------
|
| Interactions Analytics related logic
|
*/

class InteractionAnalyticsController extends \BaseController {

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
   * Show Analytics
   */
  public function getOverview()
  {
    // Security link
    $sl = Input::get('sl', '');

    // Range
    $date_start = Input::get('start', date('Y-m-d', strtotime(' - 30 day')));
    $date_end = Input::get('end', date('Y-m-d'));

    $from =  $date_start . ' 00:00:00';
    $to = $date_end . ' 23:59:59';

    $first_created = false;
    $stats_found = false;

    $all_interactions = \DB::table('interactions')->where('user_id', '=', $this->parent_user_id)
      ->select(['created_at'])
      ->orderBy('created_at', 'asc')
      ->get();

    $interactions_place = \Beacon\Model\Interaction::where('user_id', '=', $this->parent_user_id)
      ->select([\DB::raw('id')])
      ->where('created_at', '>=', $from)
      ->where('created_at', '<=', $to)
      ->whereNotNull('state')
      ->get();

    $interactions_grouped_by_day = \Beacon\Model\Interaction::where('user_id', '=', $this->parent_user_id)
      ->select([\DB::raw('COUNT(id) as total'), 'app_id', 'site_id', \DB::raw('DATE(created_at) as date')])
      ->where('created_at', '>=', $from)
      ->where('created_at', '<=', $to)
      ->orderBy('created_at', 'asc')
      ->groupBy(\DB::raw('DATE(created_at)'))
      ->get();

    $day_range = \App\Core\DateTime::dayRange($date_start, $date_end);

    // Create filled array
    $interactions_by_day = array();

    foreach ($day_range as $day)
    {
      foreach ($interactions_grouped_by_day as $interactions_day)
      {
        if ($interactions_day->date == $day)
        {
          $interactions_by_day[$day] = $interactions_day->total;
        }
      }
      if (! isset($interactions_by_day[$day])) $interactions_by_day[$day] = 0;
    }

    $interactions_this_month = \Beacon\Model\Interaction::where('user_id', '=', $this->parent_user_id)
      ->where('created_at', '>=', date('Y-m-1') . ' 00:00:00')
      ->where('created_at', '<=', date('Y-m-d') . ' 23:59:59')
      ->orderBy('created_at', 'asc')
      ->get();

    $interactions_heatmap = \Beacon\Model\Interaction::where('user_id', '=', $this->parent_user_id)
      ->select('lat', 'lng', \DB::raw('count(id) as total'))
      ->where('created_at', '>=', $from)
      ->where('created_at', '<=', $to)
      ->whereNotNull('lat')
      ->whereNotNull('lng')
      ->groupBy('lat')
      ->groupBy('lng')
      ->get();

    $interactions_platform = \Beacon\Model\Interaction::where('user_id', '=', $this->parent_user_id)
      ->select('platform', \DB::raw('count(id) as total'))
      ->where('created_at', '>=', $from)
      ->where('created_at', '<=', $to)
      ->groupBy('platform')
      ->orderBy('platform', 'asc')
      ->get();

    $interactions_model = \Beacon\Model\Interaction::where('user_id', '=', $this->parent_user_id)
      ->select('model', \DB::raw('count(id) as total'))
      ->where('created_at', '>=', $from)
      ->where('created_at', '<=', $to)
      ->groupBy('model')
      ->orderBy('model', 'asc')
      ->get();

    $interactions_app = \Beacon\Model\Interaction::where('interactions.user_id', '=', $this->parent_user_id)
      ->leftJoin('apps as a', 'interactions.app_id', '=', 'a.id')
      ->select('app_id', 'a.name as name', \DB::raw('count(interactions.id) as total'))
      ->whereNotNull('app_id')
      ->where('interactions.created_at', '>=', $from)
      ->where('interactions.created_at', '<=', $to)
      ->groupBy('interactions.app_id')
      ->orderBy('interactions.app_id', 'asc')
      ->get();

    $interactions_app_grouped_by_day = \Beacon\Model\Interaction::where('interactions.user_id', '=', $this->parent_user_id)
      ->select([\DB::raw('COUNT(DISTINCT app_id) as total'), \DB::raw('DATE(created_at) as date')])
      ->where('created_at', '>=', $from)
      ->where('created_at', '<=', $to)
      ->orderBy('created_at', 'asc')
      ->groupBy(\DB::raw('DATE(created_at)'))
      ->get();

    // Create filled array
    $interactions_app_by_day = array();

    foreach ($day_range as $day)
    {
      foreach ($interactions_app_grouped_by_day as $interactions_app_day)
      {
        if ($interactions_app_day->date == $day)
        {
          $interactions_app_by_day[$day] = $interactions_app_day->total;
        }
      }
      if (! isset($interactions_app_by_day[$day])) $interactions_app_by_day[$day] = 0;
    }

    $interactions_site = \Beacon\Model\Interaction::where('interactions.user_id', '=', $this->parent_user_id)
      ->leftJoin('sites as s', 'interactions.site_id', '=', 's.id')
      ->select('site_id', 's.name as name', \DB::raw('count(interactions.id) as total'))
      ->whereNotNull('site_id')
      ->where('interactions.created_at', '>=', $from)
      ->where('interactions.created_at', '<=', $to)
      ->groupBy('interactions.site_id')
      ->orderBy('interactions.site_id', 'asc')
      ->get();

    if (count($all_interactions) > 0)
    {
      $first_created = $all_interactions{0}->created_at;
      $stats_found = true;
    }

    $locations = \Beacon\Model\LocationGroup::where('user_id', '=', $this->parent_user_id)
      ->orderBy('name', 'asc')
      ->get();

    $location = false;

    return View::make('app.analytics.interactions', array(
      'sl' => $sl,
      'date_start' => $date_start,
      'date_end' => $date_end,
      'first_created' => $first_created,
      'stats_found' => $stats_found,
      'all_interactions' => $all_interactions,
      'interactions_place' => $interactions_place,
      'interactions_by_day' => $interactions_by_day,
      'interactions_this_month' => $interactions_this_month,
      'interactions_heatmap' => $interactions_heatmap,
      'interactions_platform' => $interactions_platform,
      'interactions_model' => $interactions_model,
      'interactions_app' => $interactions_app,
      'interactions_site' => $interactions_site,
      'locations' => $locations,
      'location' => $location,
      'day_range' => $day_range
    ));
  }

  /**
   * Show Timeline Analytics
   */
  public function getTimelineOverview()
  {
    // Security link
    $sl = Input::get('sl', '');

    // Range
    $date_start = Input::get('start', date('Y-m-d', strtotime(' - 2 day')));
    $date_end = Input::get('end', date('Y-m-d'));

    $from =  $date_start . ' 00:00:00';
    $to = $date_end . ' 23:59:59';

    $stats_found = false;

    // Get earliest date for date picker
    $first_created = false;
    $all_interactions = \DB::table('interactions')->where('user_id', '=', $this->parent_user_id)
      ->select(['created_at'])
      ->orderBy('created_at', 'asc')
      ->first();

    if (count($all_interactions) > 0)
    {
      $first_created = $all_interactions->created_at;
      $stats_found = true;
    }

    $interactions = \Beacon\Model\Interaction::where('interactions.user_id', '=', $this->parent_user_id)
      ->leftJoin('sites as s', 'interactions.site_id', '=', 's.id')
      ->leftJoin('apps as a', 'interactions.app_id', '=', 'a.id')
      ->leftJoin('scenarios as sc', 'interactions.scenario_id', '=', 'sc.id')
      ->select(
        'interactions.id',
        'interactions.device_uuid',
        'interactions.model',
        'interactions.platform',
        'interactions.state',
        'interactions.lat',
        'interactions.lng',
        'interactions.beacon',
        'interactions.geofence',
        'interactions.created_at',
        's.name as site_name',
        'a.name as app_name',
        'sc.notification',
        'sc.scenario_if_id',
        'sc.scenario_then_id',
        'sc.scenario_day_id',
        'sc.scenario_time_id',
        'sc.frequency',
        'sc.delay',
        'sc.show_image',
        'sc.template',
        'sc.open_url',
        'sc.play_sound',
        'sc.play_video'
       )
      ->whereNotNull('interactions.state')
      ->orderBy('interactions.created_at', 'asc')
      ->where('interactions.created_at', '>=', $from)
      ->where('interactions.created_at', '<=', $to)
      ->get();

    return View::make('app.analytics.timeline', array(
      'sl' => $sl,
      'date_start' => $date_start,
      'date_end' => $date_end,
      'first_created' => $first_created,
      'stats_found' => $stats_found,
      'all_interactions' => $all_interactions,
      'interactions' => $interactions
    ));
  }

  /**
   * Show Scenario Analytics
   */
  public function getScenarioOverview()
  {
    // Security link
    $sl = Input::get('sl', '');

    // Range
    $date_start = Input::get('start', date('Y-m-d', strtotime(' - 30 day')));
    $date_end = Input::get('end', date('Y-m-d'));

    $from =  $date_start . ' 00:00:00';
    $to = $date_end . ' 23:59:59';

    $stats_found = false;

    // Get earliest date for date picker
    $first_created = false;
    $all_interactions = \DB::table('interactions')->where('user_id', '=', $this->parent_user_id)
      ->select(['created_at'])
      ->orderBy('created_at', 'asc')
      ->first();

    if (count($all_interactions) > 0)
    {
      $first_created = $all_interactions->created_at;
      $stats_found = true;
    }

    return View::make('app.analytics.scenario-analytics', array(
      'sl' => $sl,
      'date_start' => $date_start,
      'date_end' => $date_end,
      'first_created' => $first_created,
      'stats_found' => $stats_found,
      'all_interactions' => $all_interactions
    ));
  }

  /**
   * Get scenario list data
   */
  public function getScenarioData()
  {
    $order_by = Input::get('order.0.column', 0);
    $order = Input::get('order.0.dir', 'asc');
    $search = Input::get('search.regex', '');
    $q = Input::get('search.value', '');
    $start = Input::get('start', 0);
    $draw = Input::get('draw', 1);
    $length = Input::get('length', 10);
    $data = array();

    $aColumn = array('interactions.platform', 'interactions.device_uuid', 'interactions.model', 'app_site', 'interactions.state', 'beacon_geofence', 'interactions.created_at');

    if($q != '')
    {
      $count = \Beacon\Model\Interaction::leftJoin('sites as s', 'interactions.site_id', '=', 's.id')
        ->leftJoin('apps as a', 'interactions.app_id', '=', 'a.id')
        ->leftJoin('scenarios as sc', 'interactions.scenario_id', '=', 'sc.id')
        ->select(
          'interactions.id'
         )
        ->where(function ($query) {
          $query->where('interactions.user_id', '=', $this->parent_user_id);
          $query->whereNotNull('interactions.state');
        })
        ->where(function ($query) use($q) {
          $query->orWhere('interactions.device_uuid', 'like', '%' . $q . '%');
          $query->orWhere('interactions.model', 'like', '%' . $q . '%');
          $query->orWhere('interactions.platform', 'like', '%' . $q . '%');
          $query->orWhere('interactions.state', 'like', '%' . $q . '%');
          $query->orWhere('interactions.beacon', 'like', '%' . $q . '%');
          $query->orWhere('interactions.geofence', 'like', '%' . $q . '%');
          $query->orWhere('s.name', 'like', '%' . $q . '%');
          $query->orWhere('a.name', 'like', '%' . $q . '%');
        })
        ->count();

      $oData = \Beacon\Model\Interaction::leftJoin('sites as s', 'interactions.site_id', '=', 's.id')
        ->leftJoin('apps as a', 'interactions.app_id', '=', 'a.id')
        ->leftJoin('scenarios as sc', 'interactions.scenario_id', '=', 'sc.id')
        ->orderBy($aColumn[$order_by], $order)
        ->select(
          'interactions.id',
          'interactions.device_uuid',
          'interactions.model',
          'interactions.platform',
          'interactions.state',
          'interactions.lat',
          'interactions.lng',
          'interactions.beacon',
          'interactions.geofence',
          'interactions.created_at',
          's.name as site_name',
          'a.name as app_name',
          'sc.notification',
          'sc.scenario_if_id',
          'sc.scenario_then_id',
          'sc.scenario_day_id',
          'sc.scenario_time_id',
          'sc.frequency',
          'sc.delay',
          'sc.show_image',
          'sc.template',
          'sc.open_url',
          'sc.play_sound',
          'sc.play_video'
        )
        ->where(function ($query) {
          $query->where('interactions.user_id', '=', $this->parent_user_id);
          $query->whereNotNull('interactions.state');
        })
        ->where(function ($query) use($q) {
          $query->orWhere('interactions.device_uuid', 'like', '%' . $q . '%');
          $query->orWhere('interactions.model', 'like', '%' . $q . '%');
          $query->orWhere('interactions.platform', 'like', '%' . $q . '%');
          $query->orWhere('interactions.state', 'like', '%' . $q . '%');
          $query->orWhere('interactions.beacon', 'like', '%' . $q . '%');
          $query->orWhere('interactions.geofence', 'like', '%' . $q . '%');
          $query->orWhere('s.name', 'like', '%' . $q . '%');
          $query->orWhere('a.name', 'like', '%' . $q . '%');
        })
        ->take($length)->skip($start)->get();
    }
    else
    {
      $count = \Beacon\Model\Interaction::where('interactions.user_id', '=', $this->parent_user_id)
        ->whereNotNull('interactions.state')
        ->count();

      $oData = \Beacon\Model\Interaction::where('interactions.user_id', '=', $this->parent_user_id)
        ->leftJoin('sites as s', 'interactions.site_id', '=', 's.id')
        ->leftJoin('apps as a', 'interactions.app_id', '=', 'a.id')
        ->leftJoin('scenarios as sc', 'interactions.scenario_id', '=', 'sc.id')
        ->select(
          'interactions.id',
          'interactions.device_uuid',
          'interactions.model',
          'interactions.platform',
          'interactions.state',
          'interactions.lat',
          'interactions.lng',
          'interactions.beacon',
          'interactions.geofence',
          'interactions.created_at',
          's.name as site_name',
          'a.name as app_name',
          'sc.notification',
          'sc.scenario_if_id',
          'sc.scenario_then_id',
          'sc.scenario_day_id',
          'sc.scenario_time_id',
          'sc.frequency',
          'sc.delay',
          'sc.show_image',
          'sc.template',
          'sc.open_url',
          'sc.play_sound',
          'sc.play_video'
         )
        ->whereNotNull('interactions.state')
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
        'device_uuid' => $row->device_uuid,
        'device_uuid_hash' => md5($row->device_uuid),
        'model' => $row->model,
        'platform' => $row->platform,
        'state_raw' => $row->state,
        'state' => trans('global.' . $row->state),
        'beacon' => $row->beacon,
        'geofence' => $row->geofence,
        'site_name' => $row->site_name,
        'app_name' => $row->app_name,
        'created_at' => $row->created_at->timezone(Auth::user()->timezone)->format(trans('i18n.dateformat_full')),
        'sl' => \App\Core\Secure::array2string(array('interaction_id' => $row->id))
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

  /**
   * Get interactions for scenario board
   */
  public function getInteractions()
  {
    // Security link    
    $sl = Input::get('sl', '');

    if($sl != '')
    {
      // Range
      //$date_start = Input::get('start', date('Y-m-d', strtotime(' - 30 day')));
      //$date_end = Input::get('end', date('Y-m-d'));

      $qs = \App\Core\Secure::string2array($sl);

      $interactions_this_month = \Beacon\Model\Interaction::where('user_id', '=', $this->parent_user_id)
        ->where('created_at', '>=', date('Y-m-1') . ' 00:00:00')
        ->where('created_at', '<=', date('Y-m-d') . ' 23:59:59')
        ->where('scenario_board_id', '<=', $qs['scenario_board_id'])
        ->orderBy('created_at', 'asc')
        ->get()->count();

      $interactions = number_format($interactions_this_month, 0, trans('i18n.dec_point'), trans('i18n.thousands_sep'));

      return \Response::json(array('interactions' => $interactions));
    }
  }
}