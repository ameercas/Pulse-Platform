<?php
namespace App\Controller;

use View, Config, Cache, App;

/*
|--------------------------------------------------------------------------
| Dashboard controller
|--------------------------------------------------------------------------
|
| Dashboard related logic
|
*/

class DashboardController extends \BaseController {

  /**
   * The layout that should be used for responses.
   */
  protected $layout = 'app.layouts.backend';

  /**
   * Instantiate a new instance.
   */
  public function __construct()
  {
    if(\Auth::check())
    {
      $this->parent_user_id = (\Auth::user()->parent_id == NULL) ? \Auth::user()->id : \Auth::user()->parent_id;
    }
    else
    {
      $this->parent_user_id = NULL;
    }
  }

  /**
   * Show main dashboard
   */
  public function getMainDashboard()
  {
    $hashPrefix = (\Config::get('system.seo', true)) ? '!' : '';
    $cms_title = \App\Core\Settings::get('cms_title', trans('global.app_title'));
    $cms_page_title = \App\Core\Settings::get('cms_page_title', trans('global.app_page_title'));
    $cms_logo = \App\Core\Settings::get('cms_logo', url('assets/images/interface/logo/icon.png'));
    $username = (\Auth::user()->first_name != '' || \Auth::user()->last_name != '') ? \Auth::user()->first_name . ' ' . \Auth::user()->last_name : \Auth::user()->username;

    $count_sites = \Web\Model\Site::where('user_id', '=', $this->parent_user_id)->count();

    if($this->parent_user_id != NULL && \Auth::user()->getRoleId() == 4)
    {
      $user_settings = json_decode(\Auth::user()->settings);
      $app_permissions = (isset($user_settings->app_permissions)) ? $user_settings->app_permissions : array();
      $count_apps = count($app_permissions);
    }
    else
    {
      $count_apps = \Mobile\Model\App::where('user_id', '=', $this->parent_user_id)->count();
      $count_boards = \Beacon\Model\ScenarioBoard::where('user_id', '=', $this->parent_user_id)->count();
      $count_geofences = \Beacon\Model\Geofence::where('user_id', '=', $this->parent_user_id)->count();
      $count_beacons = \Beacon\Model\Beacon::where('user_id', '=', $this->parent_user_id)->count();

      View::share('count_boards', $count_boards);
      View::share('count_geofences', $count_geofences);
      View::share('count_beacons', $count_beacons);
    }

    View::share('username', $username);
    View::share('count_apps', $count_apps);
    View::share('count_sites', $count_sites);
    View::share('hashPrefix', $hashPrefix);
    View::share('cms_title', $cms_title);
    View::share('cms_page_title', $cms_page_title);
    View::share('cms_logo', $cms_logo);

    return View::make('app.loader-main');
    //$this->layout->content = View::make('app.loader');
  }

  /**
   * Show dashboard partial
   */
  public function getDashboard()
  {
    $username = (\Auth::user()->first_name != '' || \Auth::user()->last_name != '') ? \Auth::user()->first_name . ' ' . \Auth::user()->last_name : \Auth::user()->username;
/*
    if($this->parent_user_id != NULL && \Auth::user()->getRoleId() == 4)
    {
      $user_settings = json_decode(\Auth::user()->settings);
      $app_permissions = (isset($user_settings->app_permissions)) ? $user_settings->app_permissions : array();

      $apps = \Mobile\Model\App::where('user_id', '=', $this->parent_user_id)->whereIn('id', $app_permissions)->orderBy('name', 'asc')->get();
    }
    else
    {
      $apps = \Mobile\Model\App::where('user_id', '=', $this->parent_user_id)->get();
    }
*/

    // Get max interactions by plan
    $plan = \User::where('id', $this->parent_user_id)->first()->plan;
    $plan->settings = ($plan->settings != '') ? json_decode($plan->settings) : new \stdClass;

    if (! isset($plan->settings->interactions)) $plan->settings->interactions = 100;
    if (! isset($plan->settings->max_boards)) $plan->settings->max_boards = 1;
    if (! isset($plan->settings->disk_space)) $plan->settings->disk_space = 1;
    if (! isset($plan->settings->max_beacons)) $plan->settings->max_beacons = 1;
    if (! isset($plan->settings->max_geofences)) $plan->settings->max_geofences = 1;
    if (! isset($plan->settings->max_sites)) $plan->settings->max_sites = 1;
    if (! isset($plan->settings->max_apps)) $plan->settings->max_apps = 1;

    if ($plan->settings->interactions == 0) $plan->settings->interactions = 1000;
    if ($plan->settings->max_boards == 0) $plan->settings->max_boards = 1000;
    if ($plan->settings->max_beacons == 0) $plan->settings->max_beacons = 1000;
    if ($plan->settings->max_geofences == 0) $plan->settings->max_geofences = 1000;
    if ($plan->settings->max_sites == 0) $plan->settings->max_sites = 1000;
    if ($plan->settings->max_apps == 0) $plan->settings->max_apps = 1000;

    $count_sites = \Web\Model\Site::where('user_id', '=', $this->parent_user_id)->count();
    $count_boards = \Beacon\Model\ScenarioBoard::where('user_id', '=', $this->parent_user_id)->count();
    $count_geofences = \Beacon\Model\Geofence::where('user_id', '=', $this->parent_user_id)->count();
    $count_beacons = \Beacon\Model\Beacon::where('user_id', '=', $this->parent_user_id)->count();

    if($this->parent_user_id != NULL && \Auth::user()->getRoleId() == 4)
    {
      $user_settings = json_decode(\Auth::user()->settings);
      $app_permissions = (isset($user_settings->app_permissions)) ? $user_settings->app_permissions : array();
      $count_apps = count($app_permissions);
    }
    else
    {
      $count_apps = \Mobile\Model\App::where('user_id', '=', $this->parent_user_id)->count();
    }

    // Range
    $date_start = \Input::get('start', date('Y-m-d', strtotime(' - 30 day')));
    $date_end = \Input::get('end', date('Y-m-d'));

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

    $app_count = \Beacon\Model\Interaction::where('interactions.user_id', '=', $this->parent_user_id)
      ->leftJoin('apps as a', 'interactions.app_id', '=', 'a.id')
      ->select('app_id', 'a.name as name', \DB::raw('count(interactions.id) as total'))
      ->whereNotNull('app_id')
      ->where('interactions.created_at', '>=', $from)
      ->where('interactions.created_at', '<=', $to)
      ->groupBy('interactions.app_id')
      ->orderBy('interactions.app_id', 'asc')
      ->get();

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

    return View::make('app.dashboard.dashboard', array(
      'username' => $username,
      'plan' => $plan,
      'date_start' => $date_start,
      'date_end' => $date_end,
      'first_created' => $first_created,
      'interactions_place' => $interactions_place,
      'interactions_by_day' => $interactions_by_day,
      'stats_found' => $stats_found,
      'all_interactions' => $all_interactions,
      'interactions_this_month' => $interactions_this_month,
      'count_apps' => $count_apps,
      'count_sites' => $count_sites,
      'count_boards' => $count_boards,
      'count_beacons' => $count_beacons,
      'count_geofences' => $count_geofences,
      'interactions_site' => $interactions_site
    ));

/*
    return View::make('app.dashboard.dashboard', array(
      'username' => $username
    ));
    */
  }

  /**
   * App JavaScript
   */
  public function getAppJs()
  {
    $translation = \Lang::get('javascript');

    $js = '_lang=[];';
    foreach($translation as $key => $val)
    {
      $js .= '_lang["' . $key . '"]="' . $val . '";';
    }

    $response = \Response::make($js);
    $response->header('Content-Type', 'application/javascript');

    return $response;
  }
}
