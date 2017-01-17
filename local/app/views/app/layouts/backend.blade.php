<!DOCTYPE html>
<!--[if IE 8]>         
<html class="ie8" lang="{{ App::getLocale() }}" ng-app="cmsApp" dir="{{ trans('i18n.dir') }}">
  <![endif]-->
  <!--[if IE 9]>         
  <html class="ie9 gt-ie8" lang="{{ App::getLocale() }}" ng-app="cmsApp" dir="{{ trans('i18n.dir') }}">
    <![endif]-->
    <!--[if gt IE 9]><!--> 
    <html class="gt-ie8 gt-ie9 not-ie" lang="{{ App::getLocale() }}" ng-app="cmsApp" dir="{{ trans('i18n.dir') }}">
      <!--<![endif]-->
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>{{ \App\Core\Settings::get('cms_title', trans('global.app_title')) }} - {{ \App\Core\Settings::get('cms_slogan', trans('global.app_title_slogan')) }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
        <link rel="shortcut icon" type="image/x-icon" href="{{ \App\Core\Settings::get('favicon', url('favicon.ico')) }}" />
        <link rel="stylesheet" href="{{ url('/assets/css/app.css?v=' . Config::get('version.version')) }}" />
        <link rel="stylesheet" href="{{ url('/assets/css/custom/app.general.css?v=' . Config::get('version.version')) }}" />
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/leaflet.css" />
        <!--[if lt IE 9]>
        <script src="{{ url('/assets/js/ie.min.js') }}"></script>
        <![endif]-->
        <script src="{{ url('/app/javascript?lang=' . \App::getLocale()) }}"></script>
        <script>var init = [];var app_root = '<?php echo url('/') ?>';var hashPrefix = '<?php echo $hashPrefix ?>';</script>
        {{ \App\Controller\HookController::hook('head'); }}
      </head>
      <body class="theme-default main-menu-animated main-navbar-fixed main-menu-fixed<?php if(\Lang::has('i18n.dir') && trans('i18n.dir') == 'rtl') echo ' right-to-left'; ?> {{ \App\Controller\HookController::hook('body_class'); }}" ng-class="{
        'page-mail': $route.current.active == 'nomargin', 
        'page-profile': $route.current.active == 'profile' || $route.current.active == 'users' || $route.current.active == 'user-edit', 
        'page-profile-user': $route.current.active == 'user-new', 
        'page-edit-site': $route.current.active_sub == 'edit-site', 
        'page-pricing': $route.current.active == 'account', 
        'page-invoice': $route.current.active_sub == 'invoice'
        }" ng-controller="MainNavCtrl">
        <div class="modal fade" id="ajax-modal" data-backdrop="static" data-keyboard="true" tabindex="-1"></div>
        <div class="modal fade" id="ajax-modal2" data-backdrop="static" data-keyboard="true" tabindex="-1"></div>
        <div id="main-wrapper">
          <div id="main-navbar" class="navbar navbar-inverse" role="navigation">
            <button type="button" id="main-menu-toggle"><i class="navbar-icon fa fa-bars icon"></i><span class="hide-menu-text">{{ trans('global.hide_menu') }}</span></button>
            <div class="navbar-inner">
              <div class="navbar-header">
                <a href="#/" class="navbar-brand" title="{{ $cms_title }}">
                  <div style="background-image:url('{{ $cms_logo }}') !important"></div>
                  <span>{{ $cms_title }}</span>
                </a>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar-collapse"><i class="navbar-icon fa fa-bars"></i></button>
              </div>
              <div id="main-navbar-collapse" class="collapse navbar-collapse main-navbar-collapse">
                <div>
                  <div class="right clearfix">
                    <ul class="nav navbar-nav pull-right right-navbar-nav">
                      <li id="msg-saved" class="bg-success">
                        <a class="no-link text-danger"><i class="fa fa-circle-o-notch fa-spin"></i>&nbsp; Saved</a>
                      </li>
                      <?php /*
                        <li>
                        	<a class="no-link"><i class="fa fa-clock-o"></i> <span id="current-time"></span></a>
                        </li>
                        <li>
                        	<a class="no-link"><i class="fa fa-calendar-o"></i> <span id="current-date"></span></a>
                        </li>
                        
                                             <li>
                                                 <form class="navbar-form pull-left">
                                                     <input type="text" class="form-control" placeholder="Search">
                                                 </form>
                                             </li>
                        */ ?>
                      <?php
                        \App\Controller\HookController::hook('top_nav');
                        
                        $languages = \App\Controller\AccountController::getLanguages();
                        
                        if(count($languages) > 1)
                        {
                        ?>
                      <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-flag"></i> {{ trans('i18n.language_title') }} <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                          <?php
                            foreach($languages as $language)
                            {
                                $active = ($language['active']) ? ' class="active"' : '';
                                echo '<li' . $active . '><a onclick="switchLanguage(\'' . $language['code'] . '\');" href="javascript:void(0);">' . $language['title'] . '</a></li>';
                            }
                            ?>
                        </ul>
                      </li>
                      <?php } ?>
                      <li class="dropdown">
                        <a class="dropdown-toggle user-menu" data-toggle="dropdown">
                        <img src="{{ App\Controller\AccountController::getAvatar(32) }}" class="avatar-32">
                        <span>{{ $username }}</span>
                        <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                          <?php
                            if(\Auth::user()->parent_id == NULL && \Auth::user()->can('user_management'))
                            {
                            ?>
                          <li><a href="#/account"><i class="dropdown-icon fa fa-rocket"></i> {{ \Auth::user()->plan->name }}</a></li>
                          <?php } ?>
                          <li><a href="#/profile"><i class="dropdown-icon ion-android-person"></i> {{ trans('global.my_profile') }}</a></li>
                          <li class="divider"></li>
                          <li><a href="{{ url('/logout') }}"><i class="dropdown-icon fa fa-power-off"></i> {{ trans('global.logout') }}</a></li>
                        </ul>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div id="main-menu" role="navigation">
            <div id="main-menu-inner">
              <div class="menu-content top" id="menu-content-demo">
                <div>
                  <div class="text-bg"><span class="text-semibold">{{ $username }}</span></div>
                  <a href="#/profile"><img src="{{ App\Controller\AccountController::getAvatar(128) }}" height="54" width="54" class="avatar-32"></a>
                  <div class="btn-group">
                    <a href="#/profile" class="btn btn-xs btn-primary btn-outline dark" title="{{ trans('global.my_profile') }}"><i class="ion-android-person"></i></a>
                    <a href="{{ url('/logout') }}" class="btn btn-xs btn-danger btn-outline dark" title="{{ trans('global.logout') }}"><i class="fa fa-power-off"></i></a>
                  </div>
                </div>
              </div>
              <ul class="navigation">
                <li ng-class="{'active': $route.current.active == 'dashboard'}">
                  <a href="#/"><i class="menu-icon ion-speedometer"></i><span class="mm-text">{{ trans('global.intro') }}</span></a>
                </li>
                <li>
                  <h4>{{ trans('global.proximity') }}</h4>
                </li>
                <?php
                  if(\Auth::user()->getRoleId() != 4)
                  {
                  ?>
                <li ng-class="{'active': $route.current.active == 'beacons'}">
                  <a href="#/beacons"><i class="menu-icon fa fa-dot-circle-o"></i><span class="mm-text">{{ trans('global.beacons') }}</span><span class="label label-primary" id="count_beacons">{{ $count_beacons }}</span></a>
                </li>
                <li ng-class="{'active': $route.current.active == 'geofences'}">
                  <a href="#/geofences"><i class="menu-icon fa fa-map-marker"></i><span class="mm-text">{{ trans('global.geofences') }}</span><span class="label label-primary" id="count_geofences">{{ $count_geofences }}</span></a>
                </li>
                <li ng-class="{'active': $route.current.active == 'scenarios'}">
                  <a href="#/boards"><i class="menu-icon ion-android-notifications"></i><span class="mm-text">{{ trans('global.scenarios') }}</span><span class="label label-primary" id="count_boards">{{ $count_boards }}</span></a>
                </li>
                <li class="mm-dropdown" ng-class="{'open': $route.current.active == 'scenario-analytics' || $route.current.active == 'interactions' || $route.current.active == 'timeline'}">
                  <a href="javascript:void(0);"><i class="menu-icon ion-stats-bars"></i><span class="mm-text">{{ trans('global.analytics') }}</span></a>
                  <ul>
                    <li ng-class="{'active': $route.current.active == 'interactions'}">
                      <a href="#/interactions"><i class="menu-icon ion-ios-analytics"></i><span class="mm-text">{{ trans('global.interactions') }}</span></a>
                    </li>
                    <li ng-class="{'active': $route.current.active == 'timeline'}">
                      <a href="#/timeline"><i class="menu-icon ion-ios-timer-outline"></i><span class="mm-text">{{ trans('global.timeline') }}</span></a>
                    </li>
                    <li ng-class="{'active': $route.current.active == 'scenario-analytics'}">
                      <a href="#/scenario-analytics"><i class="menu-icon ion-radio-waves"></i><span class="mm-text">{{ trans('global.scenarios') }}</span></a>
                    </li>
                  </ul>
                </li>
                <?php
                  }
                  ?>
                <li>
                  <h4>{{ trans('global.apps') }}</h4>
                </li>
                <li ng-class="{'active': $route.current.active == 'apps'}">
                  <a href="#/apps"><i class="menu-icon fa fa-th"></i><span class="mm-text">{{ trans('global.apps') }}</span><span class="label label-primary" id="count_apps">{{ $count_apps }}</span></a>
                </li>
                <li class="mm-dropdown" ng-class="{'open': $route.current.active == 'app-analytics' || $route.current.active == 'app-widget-data' || $route.current.active == 'app-public-users'}">
                  <a href="javascript:void(0);"><i class="menu-icon ion-erlenmeyer-flask"></i><span class="mm-text">{{ trans('global.data_and_analytics') }}</span></a>
                  <ul>
                    <li ng-class="{'active': $route.current.active == 'app-analytics'}">
                      <a href="#/app/analytics"><i class="menu-icon fa fa-line-chart"></i><span class="mm-text">{{ trans('global.analytics') }}</span></a>
                    </li>
                    <li ng-class="{'active': $route.current.active == 'app-widget-data'}">
                      <a href="#/app/widget-data"><i class="menu-icon fa fa-plug"></i><span class="mm-text">{{ trans('global.widget_data') }}</span></a>
                    </li>
                    <?php
                      if(\Auth::user()->getRoleId() != 4)
                      {
                      ?>
                    <li ng-class="{'active': $route.current.active == 'app-public-users'}">
                      <a href="#/app/public-users"><i class="menu-icon fa fa-envelope-o"></i><span class="mm-text">{{ trans('global.users') }}</span></a>
                    </li>
                    <?php
                      }
                      ?>
                  </ul>
                </li>
                <li>
                  <h4>{{ trans('global.one_pages') }}</h4>
                </li>
                <li ng-class="{'active': $route.current.active == 'web'}">
                  <a href="#/web"><i class="menu-icon fa fa-laptop" title="{{ trans('global.one_pages') }}"></i><span class="mm-text">{{ trans('global.one_pages') }}</span><span class="label label-primary" id="count_sites">{{ $count_sites }}</span></a>
                </li>
                <li class="mm-dropdown" ng-class="{'open':$route.current.active == 'web-analytics' || $route.current.active == 'leads'}">
                  <a href="javascript:void(0);"><i class="menu-icon ion-erlenmeyer-flask"></i><span class="mm-text">{{ trans('global.data_and_analytics') }}</span></a>
                  <ul>
                    <?php
                      if(\Config::get('piwik.url', '') != '')
                      {
                      ?>
                    <li ng-class="{'active': $route.current.active == 'web-analytics'}">
                      <a href="#/web/analytics"><i class="menu-icon fa fa-line-chart"></i><span class="mm-text">{{ trans('global.analytics') }}</span></a>
                    </li>
                    <?php
                      }
                      ?>
                    <li ng-class="{'active': $route.current.active == 'leads'}">
                      <a href="#/leads"><i class="menu-icon fa fa-pencil-square-o" title="{{ trans('global.form_entries') }}"></i><span class="mm-text">{{ trans('global.form_entries') }}</span></a>
                    </li>
                  </ul>
                </li>
                <li>
                  <h4>{{ trans('global.general') }}</h4>
                </li>
                <li ng-class="{'active': $route.current.active == 'media'}">
                  <a href="#/media"><i class="menu-icon ion-ios-folder-outline"></i><span class="mm-text">{{ trans('global.media') }}</span></a>
                </li>
                <li class="mm-dropdown" ng-class="{'open': $route.current.active == 'oauth' || $route.current.active == 'profile' || $route.current.active == 'campaigns' || $route.current.active == 'account' || $route.current.active == 'subscription' || $route.current.active == 'log' || $route.current.active == 'users' || $route.current.active == 'user-new' || $route.current.active == 'user-edit'}">
                  <a href="javascript:void(0);"><i class="menu-icon ion-android-options"></i><span class="mm-text">{{ trans('global.settings') }}</span></a>
                  <ul>
                    <li ng-class="{'active': $route.current.active == 'profile'}">
                      <a href="#/profile"><i class="menu-icon ion-android-person"></i><span class="mm-text">{{ trans('global.profile') }}</span></a>
                    </li>
                    <?php
                      if(\Auth::user()->can('user_management'))
                      {
                        /*
                      ?>
                    <li ng-class="{'active': $route.current.active == 'users' || $route.current.active == 'user-new' || $route.current.active == 'user-edit'}">
                      <a href="#/users"><i class="menu-icon ion-ios-people"></i><span class="mm-text">{{ trans('global.team') }}</span></a>
                    </li>
                    <?php
                      */
                      if(\Auth::user()->parent_id == NULL)
                      {
                      ?>
                    <li ng-class="{'active': $route.current.active == 'oauth'}">
                      <a href="#/oauth"><i class="menu-icon fa fa-plug"></i><span class="mm-text">{{ trans('global.apps') }}</span></a>
                    </li>
                    <li ng-class="{'active': $route.current.active == 'subscription' || $route.current.active == 'account'}">
                      <a href="#/account"><i class="menu-icon fa fa-credit-card-alt"></i><span class="mm-text">{{ trans('global.account') }}</span></a>
                    </li>
                    <?php
                      }
                      }
                      ?>
                    <?php
                      if(\Auth::user()->getRoleId() != 4)
                      {
                      ?>
                    <li ng-class="{'active': $route.current.active == 'campaigns'}">
                      <a href="#/campaigns"><i class="menu-icon fa fa-filter"></i><span class="mm-text">{{ trans('global.campaigns') }}</span></a>
                    </li>
                    <?php
                      }
                      ?>
                  </ul>
                </li>
                <?php
                  if(\Auth::user()->can('system_management'))
                  {
                  ?>
                <li class="mm-dropdown" ng-class="{'open': $route.current.active == 'admin-users' || $route.current.active == 'admin-plans' || $route.current.active == 'admin-purchases' || $route.current.active == 'admin-website' || $route.current.active == 'admin-cms' || $route.current.active == 'admin-resellers' || $route.current.active == 'admin-whitelabel'}">
                  <a href="javascript:void(0);"><i class="menu-icon ion-settings"></i><span class="mm-text">{{ trans('admin.system_administration') }}</span></a>
                  <ul>
                    <li ng-class="{'active': $route.current.active == 'admin-users'}">
                      <a href="#/admin/users"><i class="menu-icon ion-person-stalker"></i><span class="mm-text">{{ trans('admin.user_administration') }}</span></a>
                    </li>
                    <?php
                      if(! \Config::get('avangate.active', false) && \Config::get('payment-gateways.active', false))
                      {
                      ?>
                      <li ng-class="{'active': $route.current.active == 'admin-purchases'}">
                          <a href="#/admin/purchases"><i class="menu-icon fa fa-money"></i><span class="mm-text">{{ trans('admin.purchases') }}</span></a>
                      </li>
                      <?php
                      }
                      
                      if (\Auth::user()->reseller_id == 1) {
                      ?>
                    <li ng-class="{'active': $route.current.active == 'admin-resellers'}">
                      <a href="#/admin/resellers"><i class="menu-icon fa fa-user-secret"></i><span class="mm-text">{{ trans('admin.resellers') }}</span></a>
                    </li>
                    <li ng-class="{'active': $route.current.active == 'admin-plans'}">
                      <a href="#/admin/plans"><i class="menu-icon fa fa-rocket"></i><span class="mm-text">{{ trans('admin.user_plans') }}</span></a>
                    </li>
                    <?php
                      }
                      if (\Auth::user()->reseller_id != 1) {
                      ?>
                    <li ng-class="{'active': $route.current.active == 'admin-whitelabel'}">
                      <a href="#/admin/white-label"><i class="menu-icon fa fa-tag"></i><span class="mm-text">{{ trans('admin.white_label') }}</span></a>
                    </li>
                    <?php
                      }
                      /*
                      <li ng-class="{'active': $route.current.active == 'admin-website'}">
                          <a href="#/admin/website"><i class="menu-icon fa fa-globe"></i><span class="mm-text">{{ trans('admin.website') }}</span></a>
                      </li>
                      */ ?>
                    <li ng-class="{'active': $route.current.active == 'admin-cms'}">
                      <a href="#/admin/cms"><i class="menu-icon ion-paintbucket"></i><span class="mm-text">{{ trans('admin.branding') }}</span></a>
                    </li>
                  </ul>
                </li>
                <?php
                  }
                  ?>
              </ul>
            </div>
          </div>
          <div id="content-wrapper" ng-view>
            @yield('content')
          </div>
          <div id="main-menu-bg"></div>
        </div>
        <script src="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/leaflet.js"></script>
        <script src="{{ url('/assets/js/app.js?v=' . Config::get('version.version')) }}"></script>
        <script src="{{ url('/api/v1/app-edit/icon-js?v=' . Config::get('version.version')) }}"></script>
        <script src="{{ url('/assets/js/custom/app.angular.js?v=' . Config::get('version.version')) }}"></script>
        <script src="{{ url('/assets/js/custom/app.general.js?v=' . Config::get('version.version')) }}"></script>
        <script type="text/javascript">
<?php
if (ends_with(\Auth::user()->email, '@twitter.com')) {
?>
          init.push(function () {
            swal({
              title: "{{ trans('global.update_email_address') }}",
              text: "{{ trans('global.update_email_address_text') }}",
              type: "warning",
              showCancelButton: true,
              confirmButtonClass: "btn-success",
              confirmButtonText: "{{ trans('global.update_now') }}",
              cancelButtonText: "{{ trans('global.got_it') }}",
              closeOnConfirm: true,
              closeOnCancel: true
            },
            function(isConfirm)
            {
              if(isConfirm)
              {
                document.location = '#/profile';
              }
            }
           );
          });
<?php
}
?>

          window.CmsAdmin.start(init);

        </script>
        @yield('page_bottom')
      </body>
    </html>