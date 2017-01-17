<?php
namespace App\Controller;

/*
|--------------------------------------------------------------------------
| Remote controller
|--------------------------------------------------------------------------
|
| Remote api controller
|
*/

class RemoteController extends \BaseController {

  /**
   * Get scenario interaction from app
   */

  public function postScenario()
  {
    // Add interaction
    $device_uuid = \Request::get('uuid', NULL);
    $type = \Request::get('type', NULL);
    $app_id = \Request::get('app_id', NULL);
    $site_id = \Request::get('site_id', NULL);
    $scenario_id = \Request::get('scenario_id', NULL);
    $user_id = NULL;

    // App / site to user_id
    if ($app_id != NULL)
    {
      $app = \Mobile\Model\App::where('id', '=', $app_id)->first();
      $user_id = $app->user_id;
    } 
    elseif ($site_id != NULL)
    {
      $site = \Web\Model\Site::where('id', '=', $site_id)->first();
      $user_id = $site->user_id;
    }

    if ($user_id != NULL && $device_uuid != NULL && $type != NULL && $scenario_id != NULL)
    {
      // scenario_id to scenario_board_id
      $scenario_board = \Beacon\Model\Scenario::where('id', '=', $scenario_id)->first();
      $scenario_board_id = (! empty($scenario_board)) ? $scenario_board->scenario_board_id : NULL;

      $interaction = new \Beacon\Model\Interaction;

      $interaction->user_id = $user_id;
      $interaction->app_id = $app_id;
      $interaction->site_id = $site_id;
      $interaction->device_uuid = $device_uuid;
      $interaction->model = \Request::get('model', NULL);
      $interaction->platform = \Request::get('platform', NULL);
      $interaction->lat = \Request::get('lat', NULL);
      $interaction->lng = \Request::get('lng', NULL);

      $interaction->scenario_board_id = $scenario_board_id;
      $interaction->scenario_id = $scenario_id;
      $interaction->state = \Request::get('state', NULL);

      $type_id = \Request::get('type_id', NULL);

      if ($type == 'beacon')
      {
        $interaction->beacon_id = $type_id;

        $beacon = \Beacon\Model\Beacon::where('id', '=', $type_id)->first();
        $name = (! empty($beacon)) ? $beacon->name : NULL;

        $interaction->beacon = $name;
      }
      elseif ($type == 'geofence')
      {
        $interaction->geofence_id = $type_id;

        $geofence = \Beacon\Model\Geofence::where('id', '=', $type_id)->first();
        $name = (! empty($geofence)) ? $geofence->name : NULL;

        $interaction->geofence = $name;
      }

      $interaction->save();

      return \Response::json([1]);
    }
  }

  /**
   * Get handshake from hybrid / native app from POST request
   */

    public function postHandshake()
    {
        $url = \Request::get('url');
        $lat = \Request::get('lat');
        $lng = \Request::get('lng');

        $url_parts = parse_url($url);

        //\Log::info('Log message', array('local_domain' => $local_domain));

        if (! isset($url_parts['host']))
        {
            // Code instead of url has been entered
            $app = \Mobile\Model\App::where('local_domain', '=', $url)->first();
            $site = (empty($app)) ? \Web\Model\Site::where('local_domain', '=', $url)->first() : [];

            if(empty($app) && empty($site))
            {
                $response = array(
                    'content' => [
                        'found' => false,
                        'type' => NULL,
                        'name' => $url,
                        'icon' => NULL,
                        'header' => NULL
                    ]
                );

                return \Response::json($response);
            }
        }
        else
        {
            // Check for custom user domain
            $domain = str_replace('www.', '', $url_parts['host']);

            $app = \Mobile\Model\App::where('domain', '=', $domain)
                ->orWhere('domain', '=', 'www.' . $domain)
                ->first();

            $site = empty($app) ? \Web\Model\Site::where('domain', '=', $domain)
                ->orWhere('domain', '=', 'www.' . $domain)
                ->first() : [];

            if(empty($app) && empty($site))
            {
                // Check if domain is local
                if (isset($url_parts['path']))
                {
                    $local_domain = explode('/', $url_parts['path']);
                    $local_domain = end($local_domain);

                    $app = \Mobile\Model\App::where('local_domain', '=', $local_domain)->first();
                    $site = \Web\Model\Site::where('local_domain', '=', $local_domain)->first();
                }

                if($domain != str_replace('www.', '', $_SERVER['HTTP_HOST']) || (empty($app) && empty($site)))
                {
                    $response = array(
                        'content' => [
                            'found' => false,
                            'type' => NULL,
                            'name' => $url_parts['host'],
                            'icon' => NULL,
                          'header' => NULL
                        ]
                    );

                    return \Response::json($response);
                }
            }
        }

        if (! empty($app))
        {
            $response = \App\Controller\RemoteController::getScenarioBoards($app->scenarioBoards, $app, $url, 'app');
        }

        if (! empty($site))
        {
            $response = \App\Controller\RemoteController::getScenarioBoards($site->scenarioBoards, $site, $url, 'site');
        }

        // Add interaction
        $device_uuid = \Request::get('uuid', NULL);

        if ((! empty($app) || ! empty($site)) && $device_uuid != NULL)
        {
            $interaction = new \Beacon\Model\Interaction;

            $interaction->user_id = (! empty($app)) ? $app->user_id : $site->user_id;
            $interaction->app_id = (! empty($app)) ? $app->id : NULL;
            $interaction->site_id = (! empty($site)) ? $site->id : NULL;
            //$interaction->url = $url;
            $interaction->device_uuid = $device_uuid;
            $interaction->model = \Request::get('model', NULL);
            $interaction->platform = \Request::get('platform', NULL);
            $interaction->lat = \Request::get('lat', NULL);
            $interaction->lng = \Request::get('lng', NULL);

            $interaction->save();
        }

        return \Response::json($response);
    }

    /**
     * Get handshake JSON from hybrid / native app from GET request for development purposes
     */

    public function getHandshake()
    {
        $sl = \Request::input('sl', '');
        $url = \Request::input('url', '');

        if($sl != '')
        {
            if(\Auth::check())
            {
                $parent_user_id = (\Auth::user()->parent_id == NULL) ? \Auth::user()->id : \Auth::user()->parent_id;
            }
            else
            {
                die();
            }

            $qs = \App\Core\Secure::string2array($sl);
            $scenario_board = \Beacon\Model\ScenarioBoard::where('user_id', '=', $parent_user_id)->where('id', '=', $qs['scenario_board_id'])->get();
        
            $response = \App\Controller\RemoteController::getScenarioBoards($scenario_board);

            //header('Content-Type: application/json');
            return \Response::json($response);
        }

        if ($url != '')
        {
            $url_parts = parse_url($url);

            if (! isset($url_parts['host']))
            {
                // Code instead of url has been entered
                $app = \Mobile\Model\App::where('local_domain', '=', $url)->first();
                $site = (empty($app)) ? \Web\Model\Site::where('local_domain', '=', $url)->first() : [];
    
                if(empty($app) && empty($site))
                {
                    $response = array(
                        'content' => [
                            'found' => false,
                            'type' => NULL,
                            'name' => $url,
                            'icon' => NULL,
                          'header' => NULL
                        ]
                    );

                    return \Response::json($response);
                }
            }
            else
            {
                // Check for custom user domain
                $domain = str_replace('www.', '', $url_parts['host']);
    
                $app = \Mobile\Model\App::where('domain', '=', $domain)
                    ->orWhere('domain', '=', 'www.' . $domain)
                    ->first();

                $site = empty($app) ? \Web\Model\Site::where('domain', '=', $domain)
                    ->orWhere('domain', '=', 'www.' . $domain)
                    ->first() : [];

                if(empty($app) && empty($site))
                {
                    // Check if domain is local
                    if (isset($url_parts['path']))
                    {
                        $local_domain = explode('/', $url_parts['path']);
                        $local_domain = end($local_domain);
    
                        $app = \Mobile\Model\App::where('local_domain', '=', $local_domain)->first();
                        $site = \Web\Model\Site::where('local_domain', '=', $local_domain)->first();
                    }

                    if($domain != $_SERVER['HTTP_HOST'] || (empty($app) && empty($site)))
                    {
                        $response = array(
                            'content' => [
                              'found' => false,
                              'type' => NULL,
                              'name' => $url_parts['host'],
                              'icon' => NULL,
                            'header' => NULL
                            ]
                        );

                        return \Response::json($response);
                    }
                }
            }

            if (! empty($app))

            {
                $response = \App\Controller\RemoteController::getScenarioBoards($app->scenarioBoards, $app, $url, 'app');
            }

            if (! empty($site))
            {
                $response = \App\Controller\RemoteController::getScenarioBoards($site->scenarioBoards, $site, $url, 'site');
            }

            return \Response::json($response);
        }
    }

    public static function getScenarioBoards($scenarioBoards, $content = NULL, $url = NULL, $type = NULL)
    {
      $found_geofences = [];
      $found_beacons = [];
      $board = NULL;
      $board_info = [];
      $available_geofences = [];
      $available_beacons = [];
      $available_scenarios = [];
      $count_beacon = 0;
      $count_geofence = 0;
      $domain = $url;

      if ($content != NULL && $type != NULL)
      {
        if ($type == 'app')
        {
          $domain = ($content->domain != '') ? $content->domain : url('/mobile/' . $content->local_domain);
        }
        elseif ($type == 'site')
        {
          $domain = ($content->domain != '') ? $content->domain : url('/web/' . $content->local_domain);
        }
      }

        foreach ($scenarioBoards as $scenarioBoard)
        {
            $scenarios = $scenarioBoard->scenarios;
            foreach ($scenarios as $scenario)
            {
                $scenario_beacons = [];
                $beacons = $scenario->beacons;

                foreach ($beacons as $beacon)
                {
                    if ($beacon->active == 1 && ! in_array($beacon->id, $scenario_beacons))
                    {
                        array_push($scenario_beacons, $beacon->id);
                    }

                    if ($beacon->active == 1 && ! in_array($beacon->id, $found_beacons))
                    {
                        $available_beacons[$count_beacon] = array(
                            'id' => $beacon->id,
                            'identifier' => $beacon->name,
                            'uuid' => $beacon->uuid,
                            'major' => $beacon->major,
                            'minor' => $beacon->minor
                        );
                        array_push($found_beacons, $beacon->id);
                        $count_beacon++;
                    }
                }

                $scenario_geofences = [];
                $geofences = $scenario->geofences;

                foreach ($geofences as $geofence)
                {
                    if ($geofence->active == 1 && ! in_array($geofence->id, $scenario_geofences))
                    {
                        array_push($scenario_geofences, $geofence->id);
                    }

                    if ($geofence->active == 1 && ! in_array($geofence->id, $found_geofences))
                    {
                        $available_geofences[$count_geofence] = array(
                            'id' => $geofence->id,
                            'identifier' => $geofence->name,
                            'lat' => $geofence->lat,
                            'lng' => $geofence->lng,
                            'radius' => $geofence->radius
                        );
                        array_push($found_geofences, $geofence->id);
                        $count_geofence++;
                    }
                }

                // Check if scenario has (valid) output
                $scenario_has_output = true;

                switch ($scenario->scenario_then_id)
                {
                    // show_image
                    case 2: if ($scenario->show_image == '') $scenario_has_output = false; break;
                    // show_template
                    case 3: if ($scenario->template == NULL) $scenario_has_output = false; break;
                    // open_url
                    case 4: if ($scenario->open_url == '') $scenario_has_output = false; break;
                    // play_video
                    case 5: if ($scenario->play_video == '') $scenario_has_output = false; break;
                    // play_sound
                    case 6: if ($scenario->play_sound == '') $scenario_has_output = false; break;
                    // reward_points
                    case 10: if ($scenario->add_points == '') $scenario_has_output = false; break;
                    // withdraw_points
                    case 11: if ($scenario->substract_points == '') $scenario_has_output = false; break;
                    // show_app
                    case 12: if ($scenario->show_app == '') $scenario_has_output = false; break;
                    // show_site
                    case 13: if ($scenario->show_site == '') $scenario_has_output = false; break;
                }

                if ($scenario_has_output && $scenario->active == 1 && $scenario->scenario_then_id != NULL && (! empty($scenario_beacons) || ! empty($scenario_geofences)))
                {
          // Set scenario_then_id because some scenarios merge 
          // e.g. show_site is parsed to open_url
          $scenario_then_id = $scenario->scenario_then_id;
          $open_url = $scenario->open_url;

                    $template = ($scenario->template != NULL) ? url('/api/v1/remote/template/' . \App\Core\Secure::array2string(array('scenario_id' => $scenario->id))) : NULL;
                    $show_image = ($scenario->show_image != NULL) ? url('/api/v1/remote/image/' . \App\Core\Secure::array2string(array('scenario_id' => $scenario->id))) : NULL;

          // Show app
          if ($scenario_then_id == 12)
          {
            // Set to open_url
            $scenario_then_id = 4;

            // Set url
            $app = \Mobile\Model\App::where('id', '=', $scenario->show_app)->first();
            if (count($app) == 1)
            {
              $url = $app->domain();
              $url .= '?continue=1';

              if (is_numeric($scenario->show_app_page))
              {
                $app_page = \Mobile\Model\AppPage::where('id', '=', $scenario->show_app_page)->first();
                if (count($app_page) == 1)
                {
                  $url .= '#!/nav/' . $app_page->slug;
                }
              }
            }
            else
            {
              return;
            }

            $open_url = $url;
          }

          // Show site
          if ($scenario_then_id == 13)
          {
            // Set to open_url
            $scenario_then_id = 4;

            // Set url
            $site = \Web\Model\Site::where('id', '=', $scenario->show_site)->first();
            if (count($site) == 1)
            {
              $url = $site->domain();
              $url .= '?continue=1';
            }
            else
            {
              return;
            }

            $open_url = $url;
          }

          $app_id = NULL;
          $site_id = NULL;

          if ($content != NULL && $type != NULL)
          {
            if ($type == 'app')
            {
              $app_id = $content->id;
              $site_id = NULL;
            }
            elseif ($type == 'site')
            {
              $app_id = NULL;
              $site_id = $content->id;
            }
          }

                    $available_scenarios[] = array(
                        'id' => $scenario->id,
                        'app_id' => $app_id,
                        'site_id' => $site_id,
                        'scenario_if_id' => $scenario->scenario_if_id,
                        'scenario_then_id' => $scenario_then_id,
                        'scenario_day_id' => $scenario->scenario_day_id,
                        'scenario_time_id' => $scenario->scenario_time_id,
                        'time_start' => $scenario->time_start,
                        'time_end' => $scenario->time_end,
                        'date_start' => $scenario->date_start,
                        'date_end' => $scenario->date_end,
                        'frequency' => $scenario->frequency,
                        'delay' => $scenario->delay,
                        'notification' => str_replace('%', '%%', $scenario->notification),
                        'show_image' => $show_image,
                        'template' => $template,
                        'open_url' => $open_url,
                        'play_sound' => $scenario->play_sound,
                        'play_video' => $scenario->play_video,
                        'add_points' => $scenario->add_points,
                        'substract_points' => $scenario->substract_points,
                        'settings' => $scenario->settings,
                        'geofences' => $scenario_geofences,
                        'beacons' => $scenario_beacons
                    );
                }
            }

            /* Board info
             * If there're multiple boards attached to an app, those boards can
             * have different timezones. However, one location can't be in more 
             * than one timezone. So, only the timezone of the last board is used.
             */

            $board['timezone'] = $scenarioBoard->timezone;
        }

        $response = array(
            'board' => $board,
            'geofences' => $available_geofences,
            'beacons' => $available_beacons,
            'scenarios' => $available_scenarios
        );

        if ($content !== NULL)
        {
            if ($type == 'app')
            {
        $settings = json_decode($content->settings);

                $name = $content->name;
                $icon = $content->icon(120);
        $header = (isset($settings->header)) ? $settings->header : NULL;
            }

            if ($type == 'site')
            {
                $meta_title_published = $content->sitePages()->first()->meta_title_published;
                $name = ($meta_title_published != '') ? $meta_title_published : $content->sitePages()->first()->meta_title;
                $icon = url('static/app-icons/globe/120.png');
        $header = (isset($content->settings->header)) ? $content->settings->header : NULL; 
            }

            $response['content'] = [
                'found' => true,
                'type' => $type,
                'name' => $name,
                'url' => $domain,
                'icon' => $icon,
                'header' => $header
            ];
        }

        return $response;
    }

    /**
     * Show template
     */

    public function getTemplate($sl)
    {
        if($sl != '')
        {
            $qs = \App\Core\Secure::string2array($sl);
            $scenario = \Beacon\Model\Scenario::where('id', $qs['scenario_id'])->first();

            if (! empty($scenario))
            {
                return \View::make('user.app.scenario-template', [
                    'template' => $scenario->template
                ]);
            }
        }
    }

    /**
     * Show image
     */

    public function getImage($sl)
    {
        if($sl != '')
        {
            $qs = \App\Core\Secure::string2array($sl);
            $scenario = \Beacon\Model\Scenario::where('id', $qs['scenario_id'])->first();

            if (! empty($scenario))
            {
                $show_image = ($scenario->show_image != NULL) ? url($scenario->show_image) : NULL;

                return \View::make('user.app.scenario-image', [
                    'image' => $scenario->show_image
                ]);
            }
        }
    }
}