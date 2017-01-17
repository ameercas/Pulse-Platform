<?php
namespace Beacon\Controller;

use View, Auth, Input, Cache;

/*
|--------------------------------------------------------------------------
| Scenario controller
|--------------------------------------------------------------------------
|
| Scenario related logic
|
*/

class ScenarioController extends \BaseController {

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
   * Show all boards
   */
  public function getBoards()
  {
    $scenario_boards = \Beacon\Model\ScenarioBoard::where('user_id', '=', $this->parent_user_id)->orderBy('name', 'asc')->get();

    return View::make('app.beacons.boards', array(
      'scenario_boards' => $scenario_boards
    ));
  }

  /**
   * Show board
   */
  public function getBoard()
  {
    $sl = \Request::input('sl', '');

    $apps = \Mobile\Model\App::where('user_id', '=', $this->parent_user_id)->orderBy('campaign_id', 'asc')->orderBy('name', 'asc')->get();
    $sites = \Web\Model\Site::where('user_id', '=', $this->parent_user_id)->orderBy('campaign_id', 'asc')->orderBy('name', 'asc')->get();

    $campaign_old = '';
    $optgroup = array();
    $apps_select = array();
    $app_pages = array();

    foreach($apps as $app)
    {
      $campaign = $app->campaign->name;

      if($campaign != $campaign_old && $campaign_old != '')
      {
        $apps_select = array_merge($optgroup, $apps_select);
        unset($optgroup);
        $optgroup = array();
      }
      $optgroup[$campaign][$app->id] = $app->name;
      $app_pages[$app->id] = $app->appPages->toArray();

      $campaign_old = $campaign;
    }
    $apps_select = array_merge($optgroup, $apps_select);

    $campaign_old = '';
    $optgroup = array();
    $sites_select = array();

    foreach($sites as $site)
    {
      $campaign = $site->campaign->name;

      if($campaign != $campaign_old && $campaign_old != '')
      {
        $sites_select = array_merge($optgroup, $sites_select);
        unset($optgroup);
        $optgroup = array();
      }
      $optgroup[$campaign][$site->id] = $site->name;

      $campaign_old = $campaign;
    }
    $sites_select = array_merge($optgroup, $sites_select);

    if($sl != '')
    {
      // Edit board
      $qs = \App\Core\Secure::string2array($sl);
      $scenario_board = \Beacon\Model\ScenarioBoard::where('user_id', '=', $this->parent_user_id)->where('id', '=', $qs['scenario_board_id'])->first();

      // Get all beacons and groups / locations
      $geofences = \Beacon\Model\Geofence::where('user_id', '=', $this->parent_user_id)->where('active', '=', 1)->where('location_group_id', NULL)->orderBy('name', 'asc')->get();
      $beacons = \Beacon\Model\Beacon::where('user_id', '=', $this->parent_user_id)->where('active', '=', 1)->where('location_group_id', NULL)->orderBy('name', 'asc')->get();
      $location_groups = \Beacon\Model\LocationGroup::where('user_id', '=', $this->parent_user_id)->orderBy('name', 'asc')->get();

      // Get scenario statements
      $scenario_if = \Beacon\Model\ScenarioIf::all();
      $scenario_then = \Beacon\Model\ScenarioThen::where('active', 1)->orderBy('sort', 'asc')->get();
      $scenario_day = \Beacon\Model\ScenarioDay::all();
      $scenario_time = \Beacon\Model\ScenarioTime::all();

      return View::make('app.beacons.board-edit', array(
        'sl' => $sl,
        'scenario_board' => $scenario_board,
        'geofences' => $geofences,
        'beacons' => $beacons,
        'location_groups' => $location_groups,
        'apps' => $apps,
        'sites' => $sites,
        'scenario_if' => $scenario_if,
        'scenario_then' => $scenario_then,
        'scenario_day' => $scenario_day,
        'scenario_time' => $scenario_time,
        'apps_select' => $apps_select,
        'app_pages' => $app_pages,
        'sites_select' => $sites_select
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

      $plan_max_boards = (isset($plan_settings->max_boards)) ? $plan_settings->max_boards : 1;

      $boards = \Beacon\Model\ScenarioBoard::where('user_id', '=', $this->parent_user_id)->count();

      $board_limit = ($plan_max_boards != 0 && $boards >= (int) $plan_max_boards) ? true : false;

      if($board_limit)
      {
        return View::make('app.auth.upgrade');
        die();
      }

      // New board
      return View::make('app.beacons.board-new', array(
        'apps' => $apps,
        'apps_select' => $apps_select,
        'sites' => $sites,
        'sites_select' => $sites_select
      ));
    }
  }

  /**
   * New or save board
   */
  public function postBoard()
  {
    $sl = \Request::input('sl', '');
    $name = \Request::input('name');
    $apps = \Request::input('apps');
    $sites = \Request::input('sites');
    $timezone = \Request::input('timezone');
    $photo = \Request::input('photo', '');

    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
      $scenario_board = \Beacon\Model\ScenarioBoard::where('user_id', '=', $this->parent_user_id)->where('id', '=', $qs['scenario_board_id'])->first();
      $scenario_board->name = $scenario_board->name;
    }
    else
    {
      $scenario_board = new \Beacon\Model\ScenarioBoard;
      $scenario_board->name = $name;
      $scenario_board->user_id = $this->parent_user_id;

      // Attach image
      $scenario_board->photo = ($photo != '') ? url($photo) : url('/assets/images/interface/scenario-board.png');

      $scenario_board->timezone = $timezone;
    }

    if($scenario_board->save())
    {
      // Attach apps
      if($apps != '')
      {
        $scenario_board->apps()->sync($apps);
      }

      if($apps == '' && $sl != '')
      {
        $scenario_board->apps()->sync([]);
      }

      // Attach sites
      if($sites != '')
      {
        $scenario_board->sites()->sync($sites);
      }

      if($sites == '' && $sl != '')
      {
        $scenario_board->sites()->sync([]);
      }

      if($sl != '')
      {
        if(\Input::hasFile('photo'))
        {
          $response = array(
            'result' => 'success',
            'redir' => 'reload'
          );
        }
        else
        {
          $response = array(
            'result' => 'success',
            'result_msg' => trans('global.changes_saved')
          );
        }
      }
      else
      {
        $sl = \App\Core\Secure::array2string(array('scenario_board_id' => $scenario_board->id));

        $response = array(
          'result' => 'success',
          'sl' => $sl
        );
      }
    }
    else
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $scenario_board->errors()->first()
      );
    }

    return \Response::json($response);
  }

  /**
   * Show board settings modal
   */
  public function getBoardSettingsModal()
  {
    $sl = \Request::input('sl', '');
    $qs = \App\Core\Secure::string2array($sl);
    $scenario_board = \Beacon\Model\ScenarioBoard::where('id', '=', $qs['scenario_board_id'])->where('user_id', '=', $this->parent_user_id)->first();

    /* Linked apps */
    $apps = \Mobile\Model\App::where('user_id', '=', $this->parent_user_id)->orderBy('campaign_id', 'asc')->orderBy('name', 'asc')->get();

    $campaign_old = '';
    $optgroup = array();
    $apps_select = array();

    foreach($apps as $app)
    {
      $campaign = $app->campaign->name;
    
      if($campaign != $campaign_old && $campaign_old != '')
      {
        $apps_select = array_merge($optgroup, $apps_select);
        unset($optgroup);
        $optgroup = array();
      }
      $optgroup[$campaign][$app->id] = $app->name;
    
      $campaign_old = $campaign;
    }
    $apps_select = array_merge($optgroup, $apps_select);

    /* Linked sites */
    $sites = \Web\Model\Site::where('user_id', '=', $this->parent_user_id)->orderBy('campaign_id', 'asc')->orderBy('name', 'asc')->get();
  
    $campaign_old = '';
    $optgroup = array();
    $sites_select = array();

    foreach($sites as $site)
    {
      $campaign = $site->campaign->name;

      if($campaign != $campaign_old && $campaign_old != '')
      {
        $sites_select = array_merge($optgroup, $sites_select);
        unset($optgroup);
        $optgroup = array();
      }
      $optgroup[$campaign][$site->id] = $site->name;

      $campaign_old = $campaign;
    }
    $sites_select = array_merge($optgroup, $sites_select);

    return View::make('app.beacons.modal.board-settings', array(
      'sl' => $sl,
      'scenario_board' => $scenario_board,
      'apps_select' => $apps_select,
      'sites_select' => $sites_select
    ));
  }

  /**
   * Save board settings modal
   */
  public function postBoardSettings()
  {
    $sl = \Request::input('sl', '');
    $qs = \App\Core\Secure::string2array($sl);
       $scenario_board = \Beacon\Model\ScenarioBoard::where('id', '=', $qs['scenario_board_id'])->where('user_id', '=', $this->parent_user_id)->first();

    if(count($scenario_board) > 0)
    {
      $name = \Request::get('name');
      $timezone = \Request::get('timezone', 'UTC');
      $apps = \Request::input('apps', '');
      $sites = \Request::input('sites', '');
      $photo = \Request::get('photo', '');

      $scenario_board->name = $name;
      $scenario_board->timezone = $timezone;
      $scenario_board->photo = ($photo != '') ? url($photo) : STAPLER_NULL;

      if($scenario_board->save())
      {
        // Attach apps
        if($apps != '')
        {
          $scenario_board->apps()->sync($apps);
        }
  
        if($apps == '' && $sl != '')
        {
          $scenario_board->apps()->sync([]);
        }

        // Attach sites
        if($sites != '')
        {
          $scenario_board->sites()->sync($sites);
        }
  
        if($sites == '' && $sl != '')
        {
          $scenario_board->sites()->sync([]);
        }
      }
    }

    return \Response::json(array('result' => 'success'));
  }

  /**
   * Delete scenario board
   */
  public function getDeleteBoard()
  {
    $sl = \Request::input('data', '');
    $qs = \App\Core\Secure::string2array($sl);
       $scenario_board = \Beacon\Model\ScenarioBoard::where('id', '=', $qs['scenario_board_id'])->where('user_id', '=', $this->parent_user_id)->first();

    if(! empty($scenario_board))
    {
      $scenario_board->forceDelete();
    }

    return \Response::json(array('result' => 'success'));
  }

  /**
   * Save new scenario
   */
  public function postScenario()
  {
    $sl = \Request::input('sl', '');
    $qs = \App\Core\Secure::string2array($sl);
    $scenario_board = \Beacon\Model\ScenarioBoard::where('id', '=', $qs['scenario_board_id'])->where('user_id', '=', $this->parent_user_id)->first();

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
    $plan_max_scenarios = (isset($plan_settings->max_scenarios)) ? $plan_settings->max_scenarios : 3;

    $scenarios = \Beacon\Model\Scenario::where('scenario_board_id', '=', $qs['scenario_board_id'])->count();
    $scenario_limit = ($plan_max_scenarios != 0 && $scenarios >= (int) $plan_max_scenarios) ? true : false;

    if ($scenario_limit)
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => trans('admin.max_scenarios_reached')
      );
      return \Response::json($response);
      die();
    }

    if(! empty($scenario_board))
    {
      $scenario = new \Beacon\Model\Scenario;
      $scenario->scenario_board_id = $scenario_board->id;
    }

    if($scenario->save())
    {
      $sl = \App\Core\Secure::array2string(array('scenario_board_id' => $scenario_board->id, 'scenario_id' => $scenario->id));
      $response = array(
        'result' => 'success', 
        'sl' => $sl
      );
    }
    else
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $scenario->errors()->first()
      );
    }

    return \Response::json($response);
  }

  /**
   * Update scenario
   */
  public function postUpdateScenario()
  {
    $name = \Request::input('name', '');
    $value = \Request::input('value', '');
    if($value == '') $value = NULL;
    $sl = \Request::input('sl', '');
    $qs = \App\Core\Secure::string2array($sl);
    $scenario_board = \Beacon\Model\ScenarioBoard::where('id', '=', $qs['scenario_board_id'])->where('user_id', '=', $this->parent_user_id)->first();
    $scenario = $scenario_board->scenarios()->where('id', $qs['scenario_id'])->first();

    if(! empty($scenario))
    {
      if($name == 'scenario-if')
      {
        $scenario->scenario_if_id = $value;
      }
      elseif($name == 'scenario-then')
      {
        $scenario->scenario_then_id = $value;
      }
      elseif($name == 'scenario-when-date')
      {
        $scenario->scenario_day_id = $value;
      }
      elseif($name == 'scenario-when-time')
      {
        $scenario->scenario_time_id = $value;
      }
      elseif($name == 'datepicker-range')
      {
        $date_start = \Request::input('date_start', '');
        $date_end = \Request::input('date_end', '');

        if($date_start == '') $date_start = NULL;
        $scenario->date_start = $date_start;

        if($date_end == '') $date_end = NULL;
        $scenario->date_end = $date_end;
      }
      elseif($name == 'time-range')
      {
        $time_start = \Request::input('time_start', '');
        $time_end = \Request::input('time_end', '');

        if($time_start == '') $time_start = NULL;
        $scenario->time_start = $time_start;

        if($time_end == '') $time_end = NULL;
        $scenario->time_end = $time_end;
      }
      elseif($name == 'notification')
      {
        $scenario->notification = $value;
      }
      elseif($name == 'open_url')
      {
        $scenario->open_url = $value;
      }
      elseif($name == 'template')
      {
        $scenario->template = $value;
      }
      elseif($name == 'show_app')
      {
        $value = explode(',', $value);
        $scenario->show_app = (isset($value[0]) && is_numeric($value[0])) ? $value[0] : NULL;
        $scenario->show_app_page = (isset($value[1]) && is_numeric($value[1])) ? $value[1] : NULL;
      }
      elseif($name == 'show_site')
      {
        $scenario->show_site = $value;
      }
      elseif($name == 'show_image')
      {
        $scenario->show_image = $value;
      }
      elseif($name == 'config')
      {
        $frequency = \Request::input('frequency', '');
        $scenario->frequency = $frequency;

        $delay = \Request::input('delay', '');
        $scenario->delay = $delay;
      }

      $scenario->save();
    }

    return \Response::json(array('result' => 'success'));
  }


  /**
   * Update scenario beacons
   */
  public function postUpdateScenarioPlaces()
  {
    $places = \Request::input('places', '');
    $sl = \Request::input('sl', '');
    $qs = \App\Core\Secure::string2array($sl);
       $scenario_board = \Beacon\Model\ScenarioBoard::where('id', '=', $qs['scenario_board_id'])->where('user_id', '=', $this->parent_user_id)->first();
    $scenario = $scenario_board->scenarios()->where('id', $qs['scenario_id'])->first();

    if(! empty($scenario))
    {
      $geofences = array();
      $beacons = array();

      if ($places != '')
      {
        foreach($places as $place)
        {
          if (starts_with($place, 'geofence'))
          {
            $id = str_replace('geofence', '', $place);
            array_push($geofences, $id); 
          }
  
          if (starts_with($place, 'beacon'))
          {
            $id = str_replace('beacon', '', $place);
            array_push($beacons, $id); 
          }
        }
      }

      $scenario->geofences()->sync($geofences);
      $scenario->beacons()->sync($beacons);
    }

    return \Response::json(array('result' => 'success'));
  }

  /**
   * Delete scenario
   */
  public function postDeleteScenario()
  {
    $sl = \Request::input('sl', '');
    $qs = \App\Core\Secure::string2array($sl);
       $scenario_board = \Beacon\Model\ScenarioBoard::where('id', '=', $qs['scenario_board_id'])->where('user_id', '=', $this->parent_user_id)->first();

    if(! empty($scenario_board))
    {
      $scenario = $scenario_board->scenarios()->where('id', $qs['scenario_id'])->first();
      if(! empty($scenario)) $scenario->forceDelete();
    }

    $response = array(
      'result' => 'success'
    );

    return \Response::json($response);
  }

  /**
   * Update board title
   */
  public function postBoardTitle()
  {
    // Get security link with site_id, page_id
    $sl = \Input::get('pk', '');
    $qs = \App\Core\Secure::string2array($sl);

    $name = \Input::get('value', '');

    $scenario_board = \Beacon\Model\ScenarioBoard::where('id', '=', $qs['scenario_board_id'])
      ->where('user_id', '=', $this->parent_user_id)->first();

    if(! empty($scenario_board))
    {
      $scenario_board->name = $name;
      $scenario_board->save();
    }

    return \Response::json(array('status' => 'success'));
  }
}