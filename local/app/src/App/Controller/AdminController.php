<?php
namespace App\Controller;

use RedBeanPHP\R;

/*
|--------------------------------------------------------------------------
| Admin controller
|--------------------------------------------------------------------------
|
| Admin related logic
|
*/

class AdminController extends \BaseController {

  /**
   * Show purchases overview
   */
  public function getPurchases()
  {
    $users = \User::all();

    return \View::make('app.admin.purchases', array(
      'users' => $users
    ));
  }

  /**
   * Show invoice modal for admin
   */
  public function getInvoiceModal()
  {
    $sl = \Request::input('sl', NULL);
  
    if($sl != NULL)
    {
      $reseller = \App\Controller\ResellerController::get();
      $qs = \App\Core\Secure::string2array($sl);

      $order = \App\Model\Order::where('reseller_id', '=',  $reseller->id)->where('id', '=',  $qs['invoice_id'])->first();

      $expires = ($order->expires == NULL) ? '-' : \Carbon::parse($order->expires)->timezone(\Auth::user()->timezone)->format('Y-m-d');
      $invoice_date = \Carbon::parse($order->invoice_date)->timezone(\Auth::user()->timezone)->format('Y-m-d');

      return \View::make('app.admin.modal.invoice', array(
        'sl' => $sl,
        'invoice_date' => $invoice_date,
        'payment_method' => $order->payment_method,
        'cost' => $order->cost,
        'cost_str' => $order->cost_str,
        'plan_id' => $order->plan_id,
        'plan_name' => $order->plan_name,
        'expires' => $expires,
        'period' => $order->period,
        'user_name' => $order->user_name,
        'status' => $order->status
      ));
    }
  }

  /**
   * Update invoice status
   */
  public function postUpdateInvoiceStatus()
  {
    $sl = \Request::input('sl', '');
    $status = \Request::input('status', '');

    if ($sl != '')
    {
      $reseller = \App\Controller\ResellerController::get();
      $qs = \App\Core\Secure::string2array($sl);

      $order = \App\Model\Order::where('reseller_id', '=',  $reseller->id)->where('id', '=',  $qs['invoice_id'])->first();
      $order->status = $status;
      $order->save();
    }

    return \Response::json(array(
      'result' => 'success'
    ));
  }

  /**
   * Delete invoice
   */
  public function postDeleteInvoice()
  {
    $sl = \Request::input('sl', '');
    $status = \Request::input('status', '');

    if($sl != '')
    {
      $reseller = \App\Controller\ResellerController::get();
      $qs = \App\Core\Secure::string2array($sl);

      $order = \App\Model\Order::where('reseller_id', '=',  $reseller->id)->where('id', '=',  $qs['invoice_id'])->forceDelete();
    }

    return \Response::json(array(
      'result' => 'success'
    ));
  }

  /**
   * Get purchase data
   */
  public function getPurchaseData()
  {
    $reseller = \App\Controller\ResellerController::get();
    $order_by = \Input::get('order.0.column', 0);
    $order = \Input::get('order.0.dir', 'asc');
    $search = \Input::get('search.regex', '');
    $q = \Input::get('search.value', '');
    $start = \Input::get('start', 0);
    $draw = \Input::get('draw', 1);
    $length = \Input::get('length', 10);
    $data = array();

    $aColumn = array('invoice', 'user_mail', 'user_name', 'plan_name', 'expires', 'payment_method', 'cost_str', 'invoice_date', 'status');

    if($q != '')
    {
      $count = \App\Model\Order::orderBy($aColumn[$order_by], $order)
        ->where('reseller_id', '=', $reseller->id)
        ->where(function ($query) use($q) {
          $query->orWhere('invoice', 'like', '%' . $q . '%')
          ->orWhere('user_mail', 'like', '%' . $q . '%')
          ->orWhere('user_name', 'like', '%' . $q . '%')
          ->orWhere('plan_name', 'like', '%' . $q . '%')
          ->orWhere('expires', 'like', '%' . $q . '%')
          ->orWhere('payment_method', 'like', '%' . $q . '%')
          ->orWhere('cost_str', 'like', '%' . $q . '%')
          ->orWhere('invoice_date', 'like', '%' . $q . '%')
          ->orWhere('status', 'like', '%' . $q . '%');
        })
        ->count();

      $oData = \App\Model\Order::orderBy($aColumn[$order_by], $order)
        ->where('reseller_id', '=', $reseller->id)
        ->where(function ($query) use($q) {
          $query->orWhere('invoice', 'like', '%' . $q . '%')
          ->orWhere('user_mail', 'like', '%' . $q . '%')
          ->orWhere('user_name', 'like', '%' . $q . '%')
          ->orWhere('plan_name', 'like', '%' . $q . '%')
          ->orWhere('expires', 'like', '%' . $q . '%')
          ->orWhere('payment_method', 'like', '%' . $q . '%')
          ->orWhere('cost_str', 'like', '%' . $q . '%')
          ->orWhere('invoice_date', 'like', '%' . $q . '%')
          ->orWhere('status', 'like', '%' . $q . '%');
        })
        ->take($length)->skip($start)->get();
    }
    else
    {
      $count = \App\Model\Order::orderBy($aColumn[$order_by], $order)->where('reseller_id', '=', $reseller->id)->count();
      $oData = \App\Model\Order::orderBy($aColumn[$order_by], $order)->where('reseller_id', '=', $reseller->id)->take($length)->skip($start)->get();
    }

    if($length == -1) $length = $count;

    $recordsTotal = $count;
    $recordsFiltered = $count;

    $i = 1;
    foreach($oData as $row)
    {
      $expires = ($row->expires == NULL) ? '-' : \Carbon::parse($row->expires)->timezone(\Auth::user()->timezone)->format('Y-m-d');

      $data[] = array(
        'DT_RowId' => 'row_' . $i,
        'invoice' => $row->invoice,
        'user_mail' => $row->user_mail,
        'user_name' => $row->user_name,
        'plan_name' => $row->plan_name,
        'expires' => $expires,
        'payment_method' => $row->payment_method,
        'cost_str' => $row->cost_str,
        'invoice_date' => $row->invoice_datetime->format('Y-m-d'),
        'status' => $row->status,
        'sl' => \App\Core\Secure::array2string(array('invoice_id' => $row->id))
      );
      $i++;
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
   * Website settings modal
   */
  public function getWebsiteSettingsModal()
  {
    $favicon = \App\Core\Settings::get('favicon', '/favicon.ico');
    $page_title = \App\Core\Settings::get('page_title', trans('global.app_title'));
    $page_description = \App\Core\Settings::get('page_description', trans('global.app_title_slogan'));

    return \View::make('app.admin.modal.website-settings', array(
        'favicon' => $favicon,
        'page_title' => $page_title,
        'page_description' => $page_description
      ));
  }

  /**
   * Website template
   */
  public function getWebsite()
  {
    $sl = \Request::input('sl', '');
    $templates = \App\Controller\WebsiteController::loadAllTemplateConfig();
    $active_template = \App\Core\Settings::get('website_template', 'default');

    if ($sl != '')
    {
      $reseller = \App\Controller\ResellerController::get();
      $qs = \App\Core\Secure::string2array($sl);
      $template = \App\Controller\WebsiteController::loadTemplateConfig($qs['template_dir']);
      $template = $template[key($template)];

      \Lang::addNamespace('website', public_path() . '/website/' . $qs['template_dir'] . '/lang');
      \Lang::addNamespace('custom', storage_path() . '/userdata/templates/' . $qs['template_dir'] . '/' . $reseller->id . '/lang');
      \View::addNamespace('website', public_path() . '/website/' . $qs['template_dir'] . '/views');
      \Config::addNamespace('website', public_path() . '/website/' . $qs['template_dir'] . '/config');

      return \View::make('app.admin.website-edit', array(
        'sl' => $sl,
        'template' => $template,
        'active_template' => $active_template
      ));
    }
    else
    {
      return \View::make('app.admin.website', array(
        'templates' => $templates,
        'active_template' => $active_template
      ));
    }
  }

  /**
   * Set active template
   */
  public function getActivateTemplate()
  {
    $data = \Request::input('data', '');

    if ($data != '')
    {
      $qs = \App\Core\Secure::string2array($data);
      \App\Core\Settings::set('website_template', $qs['template_dir']);

      $response = array(
        'result' => 'success', 
        'template' => $qs['template_dir']
      );

      return \Response::json($response);
    }
  }

  /**
   * Update general website settings
   */
  public function postWebsiteUpdate()
  {
    \App\Core\Settings::set('favicon', \Request::input('favicon'));
    \App\Core\Settings::set('page_title', \Request::input('page_title'));
    \App\Core\Settings::set('page_description', \Request::input('page_description'));

    return \Response::json(array(
      'result' => 'success'
    ));
  }

  /**
   * Update template settings
   */
  public function postTemplateUpdate()

  {
    $sl = \Request::input('sl', '');
    $lang = \Input::except(array('sl', '_token'));

    if ($sl != '')
    {
      $reseller = \App\Controller\ResellerController::get();
      $qs = \App\Core\Secure::string2array($sl);
      $template_lang_storage = storage_path() . '/userdata/templates/' . $qs['template_dir'] . '/' . $reseller->id . '/lang/en';

      if (! \File::isDirectory($template_lang_storage))
      {
        \File::makeDirectory($template_lang_storage, 0777, true);
      }

      $lang_string = implode(', ', array_map(function ($v, $k) { return '"' . $k . '" => "' . str_replace(chr(13), '<br>', str_replace('"', '&quot;', $v)) . '"'; }, $lang, array_keys($lang)));
      $lang_string = rtrim($lang_string, ',');

      $lang_file = '<?php

return array(
  ' . $lang_string . '
);

';

      $template_file = $template_lang_storage . '/global.php';

      \File::put($template_file, $lang_file);
    }

    return \Response::json(array(
      'result' => 'success'
    ));
  }

  /**
   * General CMS settings
   */
  public function getCms()
  {
    $favicon = \App\Core\Settings::get('favicon', url('favicon.ico'));
    $cms_title = \App\Core\Settings::get('cms_title', trans('global.app_title'));
    $cms_slogan = \App\Core\Settings::get('cms_slogan', trans('global.app_title_slogan'));
    $cms_logo = \App\Core\Settings::get('cms_logo', url('assets/images/interface/logo/icon.png'));
    $cms_bg_login = \App\Core\Settings::get('cms_bg_login', url('assets/images/bg/login.jpg'));

    return \View::make('app.admin.cms', array(
      'favicon' => $favicon,
      'cms_title' => $cms_title,
      'cms_slogan' => $cms_slogan,
      'cms_logo' => $cms_logo,
      'cms_bg_login' => $cms_bg_login
    ));
  }

  /**
   * Update CMS settings
   */
  public function postCmsUpdate()
  {
    if(\Config::get('app.demo', false))
    {
      return \Response::json(array(
        'result' => 'error', 
        'result_msg' => 'You can\'t update these settings in demo mode.'
      ));
    }

    \App\Core\Settings::set('favicon', \Request::input('favicon'));
    \App\Core\Settings::set('cms_title', \Request::input('cms_title'));
    \App\Core\Settings::set('cms_slogan', \Request::input('cms_slogan'));
    \App\Core\Settings::set('cms_logo', \Request::input('cms_logo'));
    \App\Core\Settings::set('cms_bg_login', \Request::input('cms_bg_login'));

    return \Response::json(array(
      'result' => 'success'
    ));
  }

  /**
   * Show plans overview
   */
  public function getPlans()
  {
    $reseller = \App\Controller\ResellerController::get();
    // Only the master reseller can manage plans
    if ($reseller->id != 1) die();

    $plans = \App\Model\Plan::where('reseller_id', $reseller->id)->orderBy('sort')->get();

    return \View::make('app.admin.plans', array(
      'plans' => $plans
    ));
  }

  /**
   * New or edit plan
   */
  public function getPlan()
  {
    $reseller = \App\Controller\ResellerController::get();
    // Only the master reseller can manage plans
    if ($reseller->id != 1) die();

    $sl = \Request::input('sl', '');
    $widgets = \Mobile\Controller\WidgetController::loadAllWidgetConfig();

    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
         $plan = \App\Model\Plan::where('reseller_id', $reseller->id)->where('id', $qs['plan_id'])->first();
         $settings = json_decode($plan->settings);

      return \View::make('app.admin.plan-edit', array(
        'sl' => $sl,
        'plan' => $plan,
        'widgets' => $widgets,
        'settings' => $settings
      ));
    }
    else
    {
      return \View::make('app.admin.plan-new', array(
        'widgets' => $widgets
      ));
    }
  }

  /**
   * Save new plan
   */
  public function postPlanNew()
  {
    $reseller = \App\Controller\ResellerController::get();
    // Only the master reseller can manage plans
    if ($reseller->id != 1) die();

    if(\Config::get('app.demo', false))
    {
      return \Response::json(array(
        'result' => 'error', 
        'result_msg' => 'You can\'t delete, update or add plans in demo mode.'
      ));
    }

    $input = array(
      'name' => \Input::get('name'),
      'order_url' => \Input::get('order_url'),
      'upgrade_url' => \Input::get('upgrade_url'),
      'product_id' => \Input::get('product_id'),
      'interactions' => \Input::get('interactions'),
      'max_boards' => \Input::get('max_boards'),
      'max_scenarios' => \Input::get('max_scenarios'),
      'disk_space' => \Input::get('disk_space'),
      'max_apps' => \Input::get('max_apps'),
      'max_sites' => \Input::get('max_sites'),
      'max_beacons' => \Input::get('max_beacons'),
      'max_geofences' => \Input::get('max_geofences'),
      'domain' => \Input::get('domain'),
      'download' => \Input::get('download', false),
      'publish' => \Input::get('publish', true),
      'team' => \Input::get('team', false),
      'monthly' => \Input::get('monthly'),
      'annual' => \Input::get('annual'),
      'currency' => \Input::get('currency'),
      'featured' => \Input::get('featured', false)
    );

    $rules = array(
      'name' => 'required',
      'interactions' => 'required',
      'max_boards' => 'required',
      'max_scenarios' => 'required',
      'disk_space' => 'required',
      'max_apps' => 'required',
      'max_sites' => 'required',
      'max_beacons' => 'required',
      'max_geofences' => 'required'
    );

    $validator = \Validator::make($input, $rules);

    if ($validator->fails())
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $validator->messages()->first()
      );
    }
    else
    {
      $plan = new \App\Model\Plan;

      $plan->reseller_id = $reseller->id;
      $plan->name = $input['name'];
      $plan->sort = \DB::table('plans')->max('sort') + 10;
      $plan->settings = \App\Core\Settings::json(array(
        'order_url' => $input['order_url'],
        'upgrade_url' => $input['upgrade_url'],
        'product_id' => $input['product_id'],
        'interactions' => $input['interactions'],
        'max_boards' => $input['max_boards'],
        'max_scenarios' => $input['max_scenarios'],
        'disk_space' => $input['disk_space'],
        'max_apps' => $input['max_apps'],
        'max_sites' => $input['max_sites'],
        'max_beacons' => $input['max_beacons'],
        'max_geofences' => $input['max_geofences'],
        'domain' => $input['domain'],
        'download' => $input['download'],
        'publish' => $input['publish'],
        'team' => $input['team'],
        'widgets' => \Request::input('widget'),
        'monthly' => $input['monthly'],
        'annual' => $input['annual'],
        'currency' => $input['currency'],
        'featured' => $input['featured']
      ));

      if ($plan->save())
      {
        $response = array(
          'result' => 'success', 
          'result_msg' => trans('admin.new_plan_created')
        );
      }
      else
      {
        $response = array(
          'result' => 'error', 
          'result_msg' => $plan->errors()->first()
        );
      }
    }
    return \Response::json($response);
  }


  /**
   * Update existing plan
   */
  public function postPlanUpdate()
  {
    $reseller = \App\Controller\ResellerController::get();
    // Only the master reseller can manage plans
    if ($reseller->id != 1) die();

    $sl = \Input::get('sl');
    $qs = \App\Core\Secure::string2array($sl);

    if(! is_numeric($qs['plan_id'])) return 'Encryption Error.';

    if(\Config::get('app.demo', false))
    {
      return \Response::json(array(
        'result' => 'error', 
        'result_msg' => 'You can\'t delete, update or add plans in demo mode.'
      ));
    }

    $input = array(
      'name' => \Input::get('name'),
      'order_url' => \Input::get('order_url'),
      'upgrade_url' => \Input::get('upgrade_url'),
      'product_id' => \Input::get('product_id'),
      'interactions' => \Input::get('interactions'),
      'max_boards' => \Input::get('max_boards'),
      'max_scenarios' => \Input::get('max_scenarios'),
      'disk_space' => \Input::get('disk_space'),
      'max_apps' => \Input::get('max_apps'),
      'max_sites' => \Input::get('max_sites'),
      'max_beacons' => \Input::get('max_beacons'),
      'max_geofences' => \Input::get('max_geofences'),
      'domain' => \Input::get('domain'),
      'download' => \Input::get('download', false),
      'publish' => \Input::get('publish', true),
      'team' => \Input::get('team', false),
      'monthly' => \Input::get('monthly'),
      'annual' => \Input::get('annual'),
      'currency' => \Input::get('currency'),
      'featured' => \Input::get('featured', false)
    );


    $rules = array(
      'name' => 'required',
      'interactions' => 'required',
      'max_boards' => 'required',
      'max_scenarios' => 'required',
      'disk_space' => 'required',
      'max_apps' => 'required',
      'max_sites' => 'required',
      'max_beacons' => 'required',
      'max_geofences' => 'required'
    );

    $validator = \Validator::make($input, $rules);

    if($validator->fails())
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $validator->messages()->first()
      );
    }
    else
    {
      $plan = \App\Model\Plan::find($qs['plan_id']);

      $plan->name = $input['name'];
      $plan->settings = \App\Core\Settings::json(array(
        'order_url' => $input['order_url'],
        'upgrade_url' => $input['upgrade_url'],
        'product_id' => $input['product_id'],
        'interactions' => $input['interactions'],
        'max_boards' => $input['max_boards'],
        'max_scenarios' => $input['max_scenarios'],
        'disk_space' => $input['disk_space'],
        'max_apps' => $input['max_apps'],
        'max_sites' => $input['max_sites'],
        'max_beacons' => $input['max_beacons'],
        'max_geofences' => $input['max_geofences'],
        'domain' => $input['domain'],
        'download' => $input['download'],
        'publish' => $input['publish'],
        'team' => $input['team'],
        'widgets' => \Request::input('widget'),
        'monthly' => $input['monthly'],
        'annual' => $input['annual'],
        'currency' => $input['currency'],
        'featured' => $input['featured']
      ), $plan->settings);

      if($plan->save())
      {
        $response = array(
          'result' => 'success', 
          'result_msg' => trans('global.changes_saved')
        );

       }
      else
      {
        $response = array(
          'result' => 'error', 
          'result_msg' => $plan->errors()->first()
        );
      }
    }
    return \Response::json($response);
  }

  /**
   * Delete plan
   */
  public function getDeletePlan()
  {
    $reseller = \App\Controller\ResellerController::get();
    // Only the master reseller can manage plans
    if ($reseller->id != 1) die();

    if(\Config::get('app.demo', false))
    {
      return \Response::json(array(
        'result' => 'error', 
        'msg' => 'You can\'t delete a plan in demo mode.'
      ));
    }

    $sl = \Request::input('data', '');

    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
      $response = array('result' => 'success');


      // Check if there are user with this plan
      $user = \User::where('plan_id', '=', $qs['plan_id'])->first();
      if (empty($user))
      {
        $plan = \App\Model\Plan::where('reseller_id', '=',  $reseller->id)->where('id', '=',  $qs['plan_id'])->where('undeletable', 0)->forceDelete();
      }
      else
      {
        $response = array('result' => 'error', 'msg' => trans('admin.delete_plan_restricted'));
      }
    }
    return \Response::json($response);
  }

  /**
   * Sort plans
   */
  public function postPlanSort()
  {
    $reseller = \App\Controller\ResellerController::get();
    // Only the master reseller can manage plans
    if ($reseller->id != 1) die();

    // Get nodes
    $node = \Input::get('node', '');
    $node_prev = \Input::get('node_prev', '');
    $node_next = \Input::get('node_next', '');

    $node = \App\Model\Plan::where('reseller_id', '=',  $reseller->id)->where('id', '=', $node)->first();

    if (! empty($node))
    {
      // Reorder
      if(is_numeric($node_prev))
      {
        $node_prev = \App\Model\Plan::where('reseller_id', '=',  $reseller->id)->where('id', '=', $node_prev)->first();
        $new_sort = $node_prev->sort + 10;

        // Increment
        \App\Model\Plan::where('sort', '>=', $new_sort)->increment('sort', 10);
      }
      elseif(is_numeric($node_next))
      {
        $node_next = \App\Model\Plan::where('reseller_id', '=',  $reseller->id)->where('id', '=', $node_next)->first();
        $new_sort = $node_next->sort;

        // Increment
        \App\Model\Plan::where('reseller_id', '=',  $reseller->id)->where('sort', '>=', $new_sort)->increment('sort', 10);
      }

      $node->sort = $new_sort;
      $node->save();
    }

    return \Response::json(array('status' => 'success'));
  }

  /**
   * Show user overview
   */
  public function getUsers()
  {
    $reseller_comparison_operator = (\Auth::user()->reseller_id == 1) ? '>=' : '=';
    $users = \User::where('reseller_id', $reseller_comparison_operator, \Auth::user()->reseller_id)->get();

    return \View::make('app.admin.users', array(
      'users' => $users
    ));
  }

  /**
   * Show user partial
   */
  public function getUser()
  {
    $reseller_comparison_operator = (\Auth::user()->reseller_id == 1) ? '>=' : '=';

    $sl = \Request::input('sl', '');

    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
      $user = \User::where('reseller_id', $reseller_comparison_operator, \Auth::user()->reseller_id)->where('id', $qs['user_id'])->first();

      return \View::make('app.admin.user-edit', array(
        'sl' => $sl,
        'user' => $user
      ));
    }
    else
    {
      // Only the master reseller can manage plans
      if (\Auth::user()->reseller_id != 1) die();

      return \View::make('app.admin.user-new');
    }
  }

  /**
   * Login as user
   */
  public function getLoginAs($sl)
  {
    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
      $reseller_comparison_operator = (\Auth::user()->reseller_id == 1) ? '>=' : '=';

      $user = \User::where('reseller_id', $reseller_comparison_operator, \Auth::user()->reseller_id)->where('id', '=',  $qs['user_id'])->first();

      if(! empty($user))
      {
        // Set session to redirect to in case of logout
        $logout = \App\Core\Secure::array2string(['user_id' => \Auth::user()->id]);
        \Session::put('logout', $logout);

        \Auth::loginUsingId($qs['user_id']);
        return \Redirect::to('/');
      }
    }
  }

/*
  public function getLoginAs($sl)
  {
    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
      $reseller_comparison_operator = (\Auth::user()->reseller_id == 1) ? '>=' : '=';

      $user = \User::where('reseller_id', $reseller_comparison_operator, \Auth::user()->reseller_id)->where('id', '=',  $qs['user_id'])->first();

      if(! empty($user))
      {
        $reseller = \App\Model\Reseller::where('id', $user->reseller_id)->first();

        if ($reseller->settings != '')
        {
          $reseller->settings = json_decode($reseller->settings);
        }

        // Default settings
        if (! isset($reseller->settings->ssl)) $reseller->settings->ssl = false;
        $prefix = ($reseller->settings->ssl) ? 'https://' : 'http://';

        // Set session to redirect to in case of logout
        $logout = \App\Core\Secure::array2string(['user_id' => \Auth::user()->id]);
        \Session::put('logout', $logout);

        \Auth::loginUsingId($qs['user_id']);
        return \Redirect::to($prefix . $reseller->domain);
      }
    }
  }
*/

  /**
   * Delete user
   */
  public function postUserDelete()
  {
    // Only the master reseller can manage plans
    if (\Auth::user()->reseller_id != 1) die();

    $sl = \Request::input('sl', '');

    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
      $response = array('result' => 'success');

      $user = \User::where('reseller', '=',  0)->where('id', '=',  $qs['user_id'])->first();

      if(! empty($user))
      {
        $user = \User::where('id', '=',  $qs['user_id'])->where('reseller', 0)->forceDelete();
      }
      else
      {
        $response = array('result' => 'error', 'msg' => trans('global.cant_delete_owner'));
      }
    }
    return \Response::json($response);
  }

  /**
   * Get user data
   */
  public function getUserData()
  {
    $reseller = \App\Controller\ResellerController::get();
    $reseller_comparison_operator = (\Auth::user()->reseller_id == 1) ? '>=' : '=';

    $order_by = \Input::get('order.0.column', 0);
    $order = \Input::get('order.0.dir', 'asc');
    $search = \Input::get('search.regex', '');
    $q = \Input::get('search.value', '');
    $start = \Input::get('start', 0);
    $draw = \Input::get('draw', 1);
    $length = \Input::get('length', 10);
    $data = array();

    $aColumn = array('email', 'username', 'role', 'plan_id', 'expires', 'logins', 'last_login', 'confirmed', 'created_at');

    if($q != '')
    {
      $count = \User::orderBy($aColumn[$order_by], $order)
        ->where('reseller_id', $reseller_comparison_operator, \Auth::user()->reseller_id)
        ->where('parent_id', '=', NULL)
        ->where(function ($query) use($q) {
          $query->orWhere('email', 'like', '%' . $q . '%')
          ->orWhere('username', 'like', '%' . $q . '%');
        })
        ->count();

      $oData = \User::orderBy($aColumn[$order_by], $order)
        ->where('reseller_id', $reseller_comparison_operator, \Auth::user()->reseller_id)
        ->where('parent_id', '=', NULL)
        ->where(function ($query) use($q) {
          $query->orWhere('email', 'like', '%' . $q . '%')
          ->orWhere('username', 'like', '%' . $q . '%');
        })
        ->take($length)->skip($start)->get();
    }
    else
    {
      $count = \User::orderBy($aColumn[$order_by], $order)->where('reseller_id', $reseller_comparison_operator, \Auth::user()->reseller_id)->where('parent_id', '=', NULL)->count();
      $oData = \User::orderBy($aColumn[$order_by], $order)->where('reseller_id', $reseller_comparison_operator, \Auth::user()->reseller_id)->where('parent_id', '=', NULL)->take($length)->skip($start)->get();
    }

    if($length == -1) $length = $count;

    $recordsTotal = $count;
    $recordsFiltered = $count;

    $i = 1;
    foreach($oData as $row)
    {
      $expires = ($row->expires == NULL) ? '-' : $row->expires->format('Y-m-d');
      $last_login = ($row->last_login == NULL) ? '' : $row->last_login->timezone(\Auth::user()->timezone)->format('Y-m-d H:i:s');
      $id = ($row->remote_id == NULL) ? $row->id : $row->remote_id;
      $undeletable = ($row->reseller == 1) ? 1 : 0;

      if ($reseller->id != 1) {
        // Only the master reseller can delete users
        $undeletable = 1;
        $email = $row->email;
      } else {
        // Add reseller to email field
        $label = ($row->reseller_id == 1) ? 'primary' : 'danger';
        $email = '<div class="label label-' . $label . '">' . $row->reseller_id . '</div> ' . $row->email;
      }

      $plan = (isset($row->plan->name)) ? $row->plan->name : '';

      $data[] = array(
        'DT_RowId' => 'row_' . $i,
        'username' => $row->username,
        'email' => $email,
        'roles' => $row->getRolesString(),
        'plan' => $plan,
        'expires' => $expires,
        'logins' => $row->logins,
        'confirmed' => $row->confirmed,
        'last_login' => $last_login,
        'created_at' => $row->created_at->timezone(\Auth::user()->timezone)->format('Y-m-d H:i:s'),
        'sl' => \App\Core\Secure::array2string(array('user_id' => $row->id)),
        'undeletable' => $undeletable
      );
      $i++;
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
   * Save new user
   */
  public function postUserNew()
  {
    $reseller = \App\Controller\ResellerController::get();
    // Only the master reseller can manage plans
    if (\Auth::user()->reseller_id != 1) die();

    $reseller_id = $reseller->id;
    $reseller_id = (\Auth::user()->id == 1) ? \Input::get('reseller_id', $reseller_id) : $reseller_id;

    $input = array(
      'plan_id' => \Input::get('plan'),
      'expires' => \Input::get('expires', NULL),
      'role_id' => \Input::get('role', 2),
      'username' => \Input::get('username'),
      'email' => \Input::get('email'),
      'password' => \Input::get('password'),
      'language' => \Input::get('language'),
      'timezone' => \Input::get('timezone'),
      'first_name' => \Input::get('first_name'),
      'last_name' => \Input::get('last_name'),
      'confirmed' => \Input::get('confirmed', 0),
      'send_mail' => \Input::get('send_mail', 0)
    );

    $rules = array(
      'username' => 'alpha_dash|required|between:4,20|unique:users,username',
      'email' => 'required|email|unique:users,email,NULL,id,reseller_id,' . $reseller_id,
      'password' => 'required|between:5,20',
      'timezone' => 'required'
    );

    $validator = \Validator::make($input, $rules);

    if($validator->fails())
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $validator->messages()->first()
      );
    }
    else
    {
      $user = new \User;

      $user->reseller_id = $reseller_id;
      $user->plan_id = $input['plan_id'];
      $user->expires = ($input['expires'] == '') ? NULL : $input['expires'];
      $user->username = $input['username'];
      $user->email = $input['email'];
      $user->password = $input['password'];
      $user->password_confirmation = $input['password'];
      $user->confirmed = $input['confirmed'];
      $user->language = $input['language'];
      $user->timezone = $input['timezone'];
      $user->first_name = $input['first_name'];
      $user->last_name = $input['last_name'];

      if($user->save())
      {
        // Set role
        $user->attachRole($input['role_id']);

        // Send mail with login information
        $name = ($user->first_name != '' || $user->last_name != '') ? $user->first_name . ' ' . $user->last_name : $user->username;

        if($input['send_mail'] == 1)
        {
          $data = array(
            'username' => $input['username'],
            'name' => $name,
            'email' => $input['email'],
            'password' => $input['password']
          );

          // Change language to user's language for mail
          $language = \App::getLocale();
          \App::setLocale($input['language']);

          \Mail::send('emails.auth.accountcreated', $data, function($message) use($data)
          {
            $message->to($data['email'], $data['name'])->subject(trans('confide.email.account_created.subject'));
          });

          // ... And change language back
          \App::setLocale($language);
        }

        $response = array(
          'result' => 'success', 
          'result_msg' => trans('global.new_user_created')
        );
      }
      else
      {
        $response = array(
          'result' => 'error', 
          'result_msg' => $user->errors()->first()
        );
      }
    }
    return \Response::json($response);
  }

  /**
   * Update existing user
   */
  public function postUserUpdate()
  {
    $sl = \Input::get('sl');
    $qs = \App\Core\Secure::string2array($sl);

    if(! is_numeric($qs['user_id'])) return 'Encryption Error.';

    if(\Config::get('app.demo', false) && $qs['user_id'] == 1)
    {
      return \Response::json(array(
        'result' => 'error', 
        'result_msg' => 'You can\'t update the master account in demo mode.'
      ));
    }

    $reseller = \App\Controller\ResellerController::get();
    $reseller_id = \Input::get('reseller_id', NULL);
    $reseller_id = ($reseller_id != NULL && $reseller->id == 1) ? $reseller_id : $reseller->id;

    $input = array(
      'plan_id' => \Input::get('plan'),
      'expires' => \Input::get('expires', NULL),
      'role_id' => \Input::get('role', NULL),
      'username' => \Input::get('username'),
      'email' => \Input::get('email'),
      'password' => \Input::get('password'),
      'language' => \Input::get('language'),
      'timezone' => \Input::get('timezone'),
      'first_name' => \Input::get('first_name'),
      'last_name' => \Input::get('last_name'),
      'confirmed' => \Input::get('confirmed', 1),
      'send_mail' => \Input::get('send_mail', 0)
    );

    $rules = array(
      'email' => 'required|email|unique:users,email,' . $qs['user_id'] . ',id,reseller_id,' . $reseller_id,
      'password' => 'between:5,20',
      'timezone' => 'required',
      'username' => 'alpha_dash|required|between:3,20|unique:users,username,' . $qs['user_id'],
    );

    $validator = \Validator::make($input, $rules);

    if($validator->fails())
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $validator->messages()->first()
      );
    }
    else
    {
      $reseller = \App\Controller\ResellerController::get();
      $reseller_comparison_operator = (\Auth::user()->reseller_id == 1) ? '>=' : '=';

      $user = \User::where('reseller_id', $reseller_comparison_operator, \Auth::user()->reseller_id)->where('id', $qs['user_id'])->first();

      $reseller_id = \Input::get('reseller_id', NULL);

      if ($reseller_id != NULL && $reseller->id == 1) $user->reseller_id = $reseller_id;

      // Only the master reseller can manage plans and logins
      if ($reseller->id == 1) { 
        $user->plan_id = $input['plan_id'];
        $user->expires = ($input['expires'] == '') ? NULL : $input['expires'];
        if($qs['user_id'] > 1) $user->confirmed = $input['confirmed'];
      }

      $user->username = $input['username'];
      $user->first_name = $input['first_name'];
      $user->last_name = $input['last_name'];
      $user->email = $input['email'];
      $user->timezone = $input['timezone'];
      $user->language = $input['language'];

      if($input['password'] != '')
      {
        $user->password_confirmation = $input['password'];
        $user->password = $input['password'];
      }

      // Update role (if not superadmin), first detach existing
      if($qs['user_id'] > 1 && $input['role_id'] != NULL && $reseller->id == 1)
      {
        $user->roles()->detach();
        $user->attachRole($input['role_id']);
      }

      if($user->save())
      {
        $response = array(
          'result' => 'success', 
          'result_msg' => trans('global.changes_saved')
        );

        // Send mail with login information
        $name = ($user->first_name != '' || $user->last_name != '') ? $user->first_name . ' ' . $user->last_name : $user->username;

        if($input['send_mail'] == 1)
        {
          $data = array(
            'username' => $input['username'],
            'name' => $name,
            'email' => $input['email'],
            'password' => $input['password']
          );

          // Change language to user's language for mail
          $language = \App::getLocale();
          \App::setLocale($input['language']);

          \Mail::send('emails.auth.accountcreated', $data, function($message) use($data)
          {
            $message->to($data['email'], $data['name'])->subject(trans('confide.email.account_created.subject'));
          });

          // ... And change language back
          \App::setLocale($language);
        }
      }
      else
      {
        $response = array(
          'result' => 'error', 
          'result_msg' => $user->errors()->first()
        );
      }
    }
    return \Response::json($response);
  }

  /**
   * Show reseller overview
   */
  public function getResellers()
  {
    $reseller = \App\Controller\ResellerController::get();
    // Only the master reseller can manage plans
    if (\Auth::user()->reseller_id != 1) die();

    $resellers = \App\Model\Reseller::all();

    return \View::make('app.admin.resellers', array(
      'resellers' => $resellers
    ));
  }

  /**
   * Show reseller partial
   */
  public function getReseller()
  {
    $master_reseller = \App\Controller\ResellerController::get();
    $sl = \Request::input('sl', '');

    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
      $reseller = \App\Model\Reseller::where('id', $qs['reseller_id'])->first();

      $user = \User::where('reseller_id', $qs['reseller_id'])->where('reseller', 1)->first();
      $user_id = (empty($user)) ? 0: $user->id;

      return \View::make('app.admin.reseller-edit', [
        'sl' => $sl,
        'reseller' => $reseller,
        'user_id' => $user_id,
        'master_reseller' => $master_reseller
      ]);
    }
    else
    {
      return \View::make('app.admin.reseller-new', [
        'master_reseller' => $master_reseller
      ]);
    }
  }

  /**
   * Save new reseller
   */
  public function postResellerNew()
  {
    // Only the master reseller can add new resellers
    if (\Auth::user()->reseller_id != 1) die();

    $input = array(
      'user_id' => \Input::get('user_id'),
      'domain' => \Input::get('domain'),
      'active' => \Input::get('active', 1),
      'mail_from_address' => \Input::get('mail_from_address'),
      'mail_from_name' => \Input::get('mail_from_name'),
      'mail_username' => \Input::get('mail_username'),
      'mail_password' => \Input::get('mail_password'),
      'mail_host' => \Input::get('mail_host'),
      'mail_port' => \Input::get('mail_port'),
      'mail_encryption' => \Input::get('mail_encryption'),
      'app_name' => \Input::get('app_name'),
      'app_link_ios' => \Input::get('app_link_ios'),
      'app_link_android' => \Input::get('app_link_android'),
      'contact_business' => \Input::get('contact_business'),
      'contact_name' => \Input::get('contact_name'),
      'contact_mail' => \Input::get('contact_mail'),
      'contact_phone' => \Input::get('contact_phone'),
      'contact_address1' => \Input::get('contact_address1'),
      'contact_address2' => \Input::get('contact_address2'),
      'contact_zip' => \Input::get('contact_zip'),
      'contact_city' => \Input::get('contact_city'),
      'contact_country' => \Input::get('contact_country')
    );

    $rules = array(
      'domain' => 'required',
      'mail_from_address' => 'required|email',
      'mail_from_name' => 'required',
      'mail_username' => 'required',
      'mail_password' => 'required',
      'mail_host' => 'required',
      'mail_port' => 'required',
      'mail_encryption' => 'required',
      'contact_business' => 'required',
      'contact_name' => 'required',
      'contact_mail' => 'required'
    );

    $validator = \Validator::make($input, $rules);

    if($validator->fails())
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $validator->messages()->first()
      );
    }
    else
    {
      $reseller = new \App\Model\Reseller;

      $reseller->active = $input['active'];
      $reseller->domain = $input['domain'];
      $reseller->mail_from_address = $input['mail_from_address'];
      $reseller->mail_from_name = $input['mail_from_name'];
      $reseller->mail_username = \Crypt::encrypt($input['mail_username']);
      $reseller->mail_password = \Crypt::encrypt($input['mail_password']);
      $reseller->mail_host = $input['mail_host'];
      $reseller->mail_port = $input['mail_port'];
      $reseller->mail_encryption = $input['mail_encryption'];
      $reseller->app_name = $input['app_name'];
      $reseller->app_link_ios = $input['app_link_ios'];
      $reseller->app_link_android = $input['app_link_android'];
      $reseller->contact_business = $input['contact_business'];
      $reseller->contact_name = $input['contact_name'];
      $reseller->contact_mail = $input['contact_mail'];
      $reseller->contact_phone = $input['contact_phone'];
      $reseller->contact_address1 = $input['contact_address1'];
      $reseller->contact_address2 = $input['contact_address2'];
      $reseller->contact_zip = $input['contact_zip'];
      $reseller->contact_city = $input['contact_city'];
      $reseller->contact_country = $input['contact_country'];

      if($reseller->save())
      {
        if ($input['user_id'] != '')
        {
          // Set all users for this reseller to reseller = 0
          $affected = \DB::table('users')->where('reseller_id', '=', $reseller->id)->update(array('reseller' => 0));
  
          // Update selected user
          $affected = \DB::table('users')->where('id', '=', $input['user_id'])->update(array('reseller' => 1, 'reseller_id' => $reseller->id));

          // Give updated user reseller role
          $user = \User::where('reseller_id', $reseller->id)->where('id', $input['user_id'])->first();
          $user->roles()->detach();
          $user->attachRole(1); // Reseller role
        }

        $response = array(
          'result' => 'success', 
          'result_msg' => trans('admin.new_reseller_created')
        );
      }
      else
      {
        $response = array(
          'result' => 'error', 
          'result_msg' => $user->errors()->first()
        );
      }
    }
    return \Response::json($response);
  }

  /**
   * Update existing reseller
   */
  public function postResellerUpdate()
  {
    $sl = \Input::get('sl');
    $qs = \App\Core\Secure::string2array($sl);

    if(! is_numeric($qs['reseller_id'])) return 'Encryption Error.';

    if(\Config::get('app.demo', false) && $qs['reseller_id'] == 1)
    {
      return \Response::json(array(
        'result' => 'error', 
        'result_msg' => 'You can\'t update the master reseller in demo mode.'
      ));
    }

    $input = array(
      'user_id' => \Input::get('user_id'),
      'domain' => \Input::get('domain'),
      'active' => \Input::get('active', 1),
      'mail_from_address' => \Input::get('mail_from_address'),
      'mail_from_name' => \Input::get('mail_from_name'),
      'mail_username' => \Input::get('mail_username'),
      'mail_password' => \Input::get('mail_password'),
      'mail_host' => \Input::get('mail_host'),
      'mail_port' => \Input::get('mail_port'),
      'mail_encryption' => \Input::get('mail_encryption'),
      'app_name' => \Input::get('app_name'),
      'app_link_ios' => \Input::get('app_link_ios'),
      'app_link_android' => \Input::get('app_link_android'),
      'contact_business' => \Input::get('contact_business'),
      'contact_name' => \Input::get('contact_name'),
      'contact_mail' => \Input::get('contact_mail'),
      'contact_phone' => \Input::get('contact_phone'),
      'contact_address1' => \Input::get('contact_address1'),
      'contact_address2' => \Input::get('contact_address2'),
      'contact_zip' => \Input::get('contact_zip'),
      'contact_city' => \Input::get('contact_city'),
      'contact_country' => \Input::get('contact_country')
    );

    $rules = array(
      'domain' => 'required|unique:resellers,domain,' . $qs['reseller_id'],
      'mail_from_address' => 'required|email',
      'mail_from_name' => 'required',
      'mail_username' => 'required',
      'mail_password' => 'required',
      'mail_host' => 'required',
      'mail_port' => 'required',
      'mail_encryption' => 'required',
      'contact_business' => 'required',
      'contact_name' => 'required',
      'contact_mail' => 'required'
    );

    $validator = \Validator::make($input, $rules);

    if($validator->fails())
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $validator->messages()->first()
      );
    }
    else
    {
      $reseller = \App\Model\Reseller::where('id', $qs['reseller_id'])->first();

      // The master reseller can't be set inactive
      if ($reseller->id != 1) { 
        $reseller->active = $input['active'];
      }

      $reseller->domain = $input['domain'];
      $reseller->mail_from_address = $input['mail_from_address'];
      $reseller->mail_from_name = $input['mail_from_name'];
      $reseller->mail_username = \Crypt::encrypt($input['mail_username']);
      $reseller->mail_password = \Crypt::encrypt($input['mail_password']);
      $reseller->mail_host = $input['mail_host'];
      $reseller->mail_port = $input['mail_port'];
      $reseller->mail_encryption = $input['mail_encryption'];
      $reseller->app_name = $input['app_name'];
      $reseller->app_link_ios = $input['app_link_ios'];
      $reseller->app_link_android = $input['app_link_android'];
      $reseller->contact_business = $input['contact_business'];
      $reseller->contact_name = $input['contact_name'];
      $reseller->contact_mail = $input['contact_mail'];
      $reseller->contact_phone = $input['contact_phone'];
      $reseller->contact_address1 = $input['contact_address1'];
      $reseller->contact_address2 = $input['contact_address2'];
      $reseller->contact_zip = $input['contact_zip'];
      $reseller->contact_city = $input['contact_city'];
      $reseller->contact_country = $input['contact_country'];

      if ($input['user_id'] != '')
      {
        // Set all users for this reseller to reseller = 0
        $affected = \DB::table('users')->where('reseller_id', '=', $qs['reseller_id'])->update(array('reseller' => 0));

        // Update selected user
        $affected = \DB::table('users')->where('id', '=', $input['user_id'])->update(array('reseller' => 1, 'reseller_id' => $qs['reseller_id']));

        // Give updated user reseller role
        $user = \User::where('reseller_id', $qs['reseller_id'])->where('id', $input['user_id'])->first();
        $user->roles()->detach();
        $user->attachRole(1); // Reseller role
      }

      if($reseller->save())
      {
        $response = array(
          'result' => 'success', 
          'result_msg' => trans('global.changes_saved')
        );
      }
      else
      {
        $response = array(
          'result' => 'error', 
          'result_msg' => $user->errors()->first()
        );
      }
    }
    return \Response::json($response);
  }

  /**
   * Delete reseller
   */
  public function postResellerDelete()
  {
    // Only the master reseller can manage plans
    if (\Auth::user()->reseller_id != 1) die();

    $sl = \Request::input('sl', '');

    if($sl != '')
    {
      $qs = \App\Core\Secure::string2array($sl);
      $response = array('result' => 'success');

      $reseller = \App\Model\Reseller::where('id', '<>', 1)->where('id', '=',  $qs['reseller_id'])->first();

      if(! empty($reseller))
      {
        $reseller = \App\Model\Reseller::where('id', '=',  $qs['reseller_id'])->forceDelete();
      }
      else
      {
        $response = array('result' => 'error', 'msg' => trans('global.cant_delete_owner'));
      }
    }
    return \Response::json($response);
  }

  /**
   * Get reseller data
   */
  public function getResellerData()
  {
    // system_management required
    if(! \Auth::user()->can('system_management')) die();

    $order_by = \Input::get('order.0.column', 0);
    $order = \Input::get('order.0.dir', 'asc');
    $search = \Input::get('search.regex', '');
    $q = \Input::get('search.value', '');
    $start = \Input::get('start', 0);
    $draw = \Input::get('draw', 1);
    $length = \Input::get('length', 10);
    $data = array();

    $aColumn = array('domain', 'mail_from_address', 'contact_name', 'contact_business', 'reseller', 'created_at', 'active');

    if($q != '')
    {
      $count = \App\Model\Reseller::orderBy($aColumn[$order_by], $order)
        ->where('domain', 'like', '%' . $q . '%')
        ->where('mail_from_address', 'like', '%' . $q . '%')
        ->where('contact_name', 'like', '%' . $q . '%')
        ->where('contact_business', 'like', '%' . $q . '%')
        ->count();

      $oData = \App\Model\Reseller::orderBy($aColumn[$order_by], $order)
        ->where('domain', 'like', '%' . $q . '%')
        ->where('mail_from_address', 'like', '%' . $q . '%')
        ->where('contact_name', 'like', '%' . $q . '%')
        ->where('contact_business', 'like', '%' . $q . '%')
        ->take($length)->skip($start)->get();
    }
    else
    {
      $count = \App\Model\Reseller::orderBy($aColumn[$order_by], $order)->count();
      $oData = \App\Model\Reseller::orderBy($aColumn[$order_by], $order)->take($length)->skip($start)->get();
    }

    if($length == -1) $length = $count;

    $recordsTotal = $count;
    $recordsFiltered = $count;

    $i = 1;
    foreach($oData as $row)
    {
      // The master reseller can't be deleted
      $undeletable = ($row->id == 1) ? 1 : 0;

      // Get main resller
      $reseller = '';
      $reseller_sl = '';
      $user = \User::where('reseller_id', $row->id)->where('reseller', 1)->first();
      if (! empty($user)) {
        $reseller_sl = \App\Core\Secure::array2string(array('user_id' => $user->id));
        $reseller = $user->email;
      }

      $data[] = array(
        'DT_RowId' => 'row_' . $i,
        'domain' => $row->domain,
        'mail_from_address' => $row->mail_from_address,
        'contact_name' => $row->contact_name,
        'contact_business' => $row->contact_business,
        'reseller' => $reseller,
        'reseller_sl' => $reseller_sl,
        'active' => $row->active,
        'created_at' => $row->created_at->timezone(\Auth::user()->timezone)->format('Y-m-d H:i:s'),
        'sl' => \App\Core\Secure::array2string(array('reseller_id' => $row->id)),
        'undeletable' => $undeletable
      );
      $i++;
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
   * Show white label account partial
   */
  public function getWhiteLabel()
  {
    $master_reseller = \App\Controller\ResellerController::get();

    $reseller = \App\Model\Reseller::where('id', \Auth::user()->reseller_id)->first();

    $user = \User::where('reseller_id', \Auth::user()->reseller_id)->where('reseller', 1)->first();
    $user_id = (empty($user)) ? 0: $user->id;

    return \View::make('app.admin.white-label', [
      'reseller' => $reseller,
      'user_id' => $user_id,
      'master_reseller' => $master_reseller
    ]);
  }

  /**
   * Update existing white label account
   */
  public function postWhiteLabel()
  {
    // system_management required
    if(! \Auth::user()->can('system_management')) die();

    $input = array(
      'user_id' => \Input::get('user_id'),
      'domain' => trim(\Input::get('domain'), '/'),
      'active' => \Input::get('active', 1),
      'mail_from_address' => \Input::get('mail_from_address'),
      'mail_from_name' => \Input::get('mail_from_name'),
      'mail_username' => \Input::get('mail_username'),
      'mail_password' => \Input::get('mail_password'),
      'mail_host' => \Input::get('mail_host'),
      'mail_port' => \Input::get('mail_port'),
      'mail_encryption' => \Input::get('mail_encryption'),
      'app_name' => \Input::get('app_name'),
      'app_link_ios' => \Input::get('app_link_ios'),
      'app_link_android' => \Input::get('app_link_android'),
      'contact_business' => \Input::get('contact_business'),
      'contact_name' => \Input::get('contact_name'),
      'contact_mail' => \Input::get('contact_mail'),
      'contact_phone' => \Input::get('contact_phone'),
      'contact_address1' => \Input::get('contact_address1'),
      'contact_address2' => \Input::get('contact_address2'),
      'contact_zip' => \Input::get('contact_zip'),
      'contact_city' => \Input::get('contact_city'),
      'contact_country' => \Input::get('contact_country')
    );

    $rules = array(
      'domain' => 'required|unique:resellers,domain,' . \Auth::user()->reseller_id,
      'mail_from_address' => 'required|email',
      'mail_from_name' => 'required',
      'mail_username' => 'required',
      'mail_password' => 'required',
      'mail_host' => 'required',
      'mail_port' => 'required',
      'mail_encryption' => 'required',
      'contact_business' => 'required',
      'contact_name' => 'required',
      'contact_mail' => 'required'
    );

    $validator = \Validator::make($input, $rules);

    if($validator->fails())
    {
      $response = array(
        'result' => 'error', 
        'result_msg' => $validator->messages()->first()
      );
    }
    else
    {
      $reseller = \App\Model\Reseller::where('id', \Auth::user()->reseller_id)->first();

      // The master reseller can't be set inactive
      if ($reseller->id != 1) { 
        $reseller->active = $input['active'];
      }

      $reseller->domain = $input['domain'];
      $reseller->mail_from_address = $input['mail_from_address'];
      $reseller->mail_from_name = $input['mail_from_name'];
      $reseller->mail_username = \Crypt::encrypt($input['mail_username']);
      $reseller->mail_password = \Crypt::encrypt($input['mail_password']);
      $reseller->mail_host = $input['mail_host'];
      $reseller->mail_port = $input['mail_port'];
      $reseller->mail_encryption = $input['mail_encryption'];
      $reseller->app_name = $input['app_name'];
      $reseller->app_link_ios = $input['app_link_ios'];
      $reseller->app_link_android = $input['app_link_android'];
      $reseller->contact_business = $input['contact_business'];
      $reseller->contact_name = $input['contact_name'];
      $reseller->contact_mail = $input['contact_mail'];
      $reseller->contact_phone = $input['contact_phone'];
      $reseller->contact_address1 = $input['contact_address1'];
      $reseller->contact_address2 = $input['contact_address2'];
      $reseller->contact_zip = $input['contact_zip'];
      $reseller->contact_city = $input['contact_city'];
      $reseller->contact_country = $input['contact_country'];

      if ($input['user_id'] != '')
      {
        // Set all users for this reseller to reseller = 0
        $affected = \DB::table('users')->where('reseller_id', '=', \Auth::user()->reseller_id)->update(array('reseller' => 0));

        // Update selected user
        $affected = \DB::table('users')->where('id', '=', $input['user_id'])->update(array('reseller' => 1, 'reseller_id' => \Auth::user()->reseller_id));
      }

      if($reseller->save())
      {
        $response = array(
          'result' => 'success', 
          'result_msg' => trans('global.changes_saved')
        );
      }
      else
      {
        $response = array(
          'result' => 'error', 
          'result_msg' => $user->errors()->first()
        );
      }
    }
    return \Response::json($response);
  }
}