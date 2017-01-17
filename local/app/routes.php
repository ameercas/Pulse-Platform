<?php
/*
 |--------------------------------------------------------------------------
 | Installation check (database, permissions)
 |--------------------------------------------------------------------------
 */

\App\Controller\InstallationController::check();

/*
 |--------------------------------------------------------------------------
 | Disable database query log for improved performance
 |--------------------------------------------------------------------------
 */

\DB::connection()->disableQueryLog();

/*
 |--------------------------------------------------------------------------
 | Language
 |--------------------------------------------------------------------------
 */

$app_language = \App\Controller\AccountController::appLanguage();
App::setLocale($app_language);

/*
 |--------------------------------------------------------------------------
 | Globals
 |--------------------------------------------------------------------------
 */

$url_parts = parse_url(URL::current());

/*
 |--------------------------------------------------------------------------
 | Check for reseller or custom domain
 |--------------------------------------------------------------------------
 */

$reseller = \App\Controller\ResellerController::get();

$custom_app = array();
$custom_site = array();

if ($reseller !== false)
{
  // Reseller global settings
  if ($reseller->mail_host != '') \Config::set('mail.host', $reseller->mail_host);
  if ($reseller->mail_port != '') \Config::set('mail.port', $reseller->mail_port);
  if ($reseller->mail_encryption != '') \Config::set('mail.encryption', $reseller->mail_encryption);
  if ($reseller->mail_username != '') \Config::set('mail.username', \Crypt::decrypt($reseller->mail_username));
  if ($reseller->mail_password != '') \Config::set('mail.password', \Crypt::decrypt($reseller->mail_password));
  if ($reseller->mail_from_address != '') \Config::set('mail.from', array('address' => $reseller->mail_from_address, 'name' => $reseller->mail_from_name));
}

$domain = str_replace('www.', '', $url_parts['host']);

$custom_app = \Mobile\Model\App::where('domain', $domain)
  ->orWhere('domain', 'www.' . $domain)
  ->first();

$custom_site = \Web\Model\Site::where('domain', $domain)
  ->orWhere('domain', 'www.' . $domain)
  ->first();

/*
 |--------------------------------------------------------------------------
 | Front end website
 |--------------------------------------------------------------------------
 */

Route::get('/', function() use($url_parts, $custom_app, $custom_site, $reseller)
{
  if ($reseller === false && count($custom_app) == 0 && count($custom_site) == 0)
  {
    return \Response::view('app.errors.reseller-404', [], 404);
  }
  elseif (count($custom_app) > 0)
  {
    // Naked or www domain?
    if (substr($custom_app->domain, 0, 4) == 'www.' && substr($url_parts['host'], 0, 4) != 'www.')
    {
      return \Redirect::to($url_parts['scheme'] . '://' . $custom_app->domain, 301);
    } 
    elseif (substr($custom_app->domain, 0, 4) != 'www.' && substr($url_parts['host'], 0, 4) == 'www.')
    {
      return \Redirect::to($url_parts['scheme'] . '://' . $custom_app->domain, 301);
    }

    // App
    $language = \App\Controller\AccountController::siteLanguage($custom_app);
    App::setLocale($language);

    return App::make('\Mobile\Controller\MobileController')->showApp($custom_app->local_domain);
  }
  elseif (count($custom_site) > 0)
  {
    // Naked or www domain?
    if (substr($custom_site->domain, 0, 4) == 'www.' && substr($url_parts['host'], 0, 4) != 'www.')
    {
      return \Redirect::to($url_parts['scheme'] . '://' . $custom_site->domain, 301);
    } 
    elseif (substr($custom_site->domain, 0, 4) != 'www.' && substr($url_parts['host'], 0, 4) == 'www.')
    {
      return \Redirect::to($url_parts['scheme'] . '://' . $custom_site->domain, 301);
    }

    // Site
    $language = \App\Controller\AccountController::siteLanguage($custom_site);
    App::setLocale($language);

    return App::make('\Web\Controller\SiteController')->showSite($custom_site->local_domain);
  }
  else
  {
    // Public facing website
    $globals = App::make('globals');

    if (! \Auth::check())
    {
      if ($reseller->settings->ssl && ! $globals->ssl)
      {
        return \Redirect::to(secure_url('login'));
      }
      else
      {
        return \Redirect::to('login');
      }
    }
    else
    {
      // Force reseller SSL if enabled by settings
      if ($reseller->settings->ssl && ! $globals->ssl)
      {
        return \Redirect::to(secure_url('/'));
      }
      else
      {
        return App::make('\App\Controller\DashboardController')->getMainDashboard();
      }
    }
  }
});

/*
 |--------------------------------------------------------------------------
 | API
 |--------------------------------------------------------------------------
 */

Route::group(array('prefix' => 'api/v1'), function()
{
  Route::controller('admin',                  'App\Controller\AdminController');
  Route::controller('account',                'App\Controller\AccountController');
  Route::controller('log',                    'App\Controller\LogController');
  Route::controller('remote',                 'App\Controller\RemoteController');
  Route::controller('campaign',               'Campaign\Controller\CampaignController');
  Route::controller('mobile',                 'Mobile\Controller\MobileController');
  Route::controller('app',                    'Mobile\Controller\AppController');
  Route::controller('app-edit',               'Mobile\Controller\AppEditController');
  Route::controller('app-export',             'Mobile\Controller\ExportController');
  Route::controller('app-asset',              'Mobile\Controller\AssetController');
  Route::controller('app-theme',              'Mobile\Controller\ThemeController');
  Route::controller('app-remote',             'Mobile\Controller\RemoteController');
  Route::controller('app-analytics',          'Analytics\Controller\AppAnalyticsController');
  Route::controller('app-track',              'Analytics\Controller\AppTrackController');
  Route::controller('geofence',               'Beacon\Controller\GeofenceController');
  Route::controller('beacon',                 'Beacon\Controller\BeaconController');
  Route::controller('interaction-analytics',  'Analytics\Controller\InteractionAnalyticsController');
  Route::controller('scenario',               'Beacon\Controller\ScenarioController');
  Route::controller('help',                   'App\Core\Help');
  Route::controller('widget',                 'Mobile\Controller\WidgetController');
  Route::controller('thumb',                  'App\Core\Thumb');
  Route::controller('oauth',                  'App\Controller\oAuthController');
  Route::controller('website',                'App\Controller\WebsiteController');
  Route::controller('translation',            'App\Controller\TranslationController');
  Route::controller('site',                   'Web\Controller\SiteController');
  Route::controller('site-export',            'Web\Controller\ExportController');
  Route::controller('site-edit',              'Web\Controller\SiteEditController');
  Route::controller('site-analytics',         'Analytics\Controller\WebAnalyticsController');
  Route::controller('lead',                   'Lead\Controller\LeadController');
  Route::controller('hook',                   'App\Controller\HookController');
  Route::controller('avangate',               'App\Controller\AvangateController');
  Route::controller('jwt',                    'App\Controller\JwtController');
});

/*
 |--------------------------------------------------------------------------
 | App
 |--------------------------------------------------------------------------
 */

// Dashboard
//Route::get( '/',                                   'App\Controller\DashboardController@getMainDashboard');
Route::get( '/app/dashboard',                      'App\Controller\DashboardController@getDashboard');
Route::get( '/app/javascript',                     'App\Controller\DashboardController@getAppJs');

// Scenarios
Route::get( '/app/boards',                         'Beacon\Controller\ScenarioController@getBoards');
Route::get( '/app/board',                          'Beacon\Controller\ScenarioController@getBoard');
Route::get( '/app/modal/beacon/board-settings',    'Beacon\Controller\ScenarioController@getBoardSettingsModal');
Route::get( '/app/scenarios',                      'Beacon\Controller\ScenarioController@getScenarios');
Route::get( '/app/scenario',                       'Beacon\Controller\ScenarioController@getScenario');

// Proximity analytics
Route::get( '/app/interactions',                   'Analytics\Controller\InteractionAnalyticsController@getOverview');
Route::get( '/app/timeline',                       'Analytics\Controller\InteractionAnalyticsController@getTimelineOverview');
Route::get( '/app/scenario-analytics',             'Analytics\Controller\InteractionAnalyticsController@getScenarioOverview');

// Geofences
Route::get( '/app/geofences',                      'Beacon\Controller\GeofenceController@getGeofences');
Route::get( '/app/geofence',                       'Beacon\Controller\GeofenceController@getGeofence');

// Beacons
Route::get( '/app/beacons',                        'Beacon\Controller\BeaconController@getBeacons');
Route::get( '/app/beacon',                         'Beacon\Controller\BeaconController@getBeacon');
Route::get( '/app/modal/beacon-import',            'Beacon\Controller\BeaconController@getBeaconImportModal');

// Apps
Route::get( '/app/mobile',                         'Mobile\Controller\AppController@getApps');
Route::get( '/app/app',                            'Mobile\Controller\AppController@getApp');
Route::get( '/app/modal/mobile/qr',                'Mobile\Controller\AppController@getQrModal');
Route::get( '/app/modal/mobile/app-redirect',      'Mobile\Controller\AppController@getAppRedirectModal');
Route::get( '/app/modal/mobile/app-settings',      'Mobile\Controller\AppController@getAppSettingsModal');
Route::get( '/app/modal/mobile/app-export',        'Mobile\Controller\AppController@getAppExportModal');
Route::get( '/app/modal/mobile/app-export/keys',   'Mobile\Controller\AppController@getAppExportKeysModal');

// App front end
Route::get( '/mobile',                             'Mobile\Controller\AppController@newApp');
Route::get( '/mobile/{local_domain}',              'Mobile\Controller\MobileController@showApp');
Route::get( '/sitemap.xml',                        'Mobile\Controller\SitemapController@showSitemap');
Route::get( '/mobile/{local_domain}/sitemap.xml',  'Mobile\Controller\SitemapController@showSitemap');
Route::get( '/system.html',                        'Mobile\Controller\MobileController@showSystemTemplates');
Route::get( '/mobile/{local_domain}/manifest.json',   'Mobile\Controller\PwaController@showManifest');
Route::get( '/sw.js',                                 'Mobile\Controller\PwaController@showServiceWorker');

Route::get( '/m/{local_domain}',                   'Mobile\Controller\MobileController@showApp');
Route::get( '/m/{local_domain}/sitemap.xml',       'Mobile\Controller\SitemapController@showSitemap');


Route::any( '/mobile/reset_password/{token}',      'Mobile\Controller\MobileController@showResetPass');

// App Analytics
Route::get( '/app/app/analytics',                  'Analytics\Controller\AppAnalyticsController@getStats');
Route::get( '/app/app/widget-data',                'Analytics\Controller\AppAnalyticsController@getData');
Route::get( '/app/app/public-users',               'Analytics\Controller\AppAnalyticsController@getUsers');

// Web
Route::get( '/app/web',                            'Web\Controller\SiteController@getSites');
Route::get( '/app/site',                           'Web\Controller\SiteController@getSite');
Route::get( '/app/modal/web/qr',                   'Web\Controller\SiteController@getQrModal');
Route::get( '/app/modal/web/site-settings',        'Web\Controller\SiteController@getSiteSettingsModal');

// Web Analytics
Route::get( '/app/web/analytics',                  'Analytics\Controller\WebAnalyticsController@getWeb');

// Web preview, edit and view
Route::get( '/web/view/{theme_dir}',               'Web\Controller\SiteController@getView');
Route::get( '/web/view/{type_dir}/{theme_dir}',    'Web\Controller\SiteController@previewTemplate');
Route::get( '/web/{local_domain}',                 'Web\Controller\SiteController@showSite');

// Inline site editor
Route::get( '/edit/site',                          'Web\Controller\SiteController@getSiteEditor');
Route::get( '/app/modal/web/form-editor',          'Web\Controller\SiteEditController@getFormEditModal');
Route::get( '/app/modal/web/iframe-editor',        'Web\Controller\SiteEditController@getIframeEditModal');
Route::get( '/app/modal/web/link-editor',          'Web\Controller\SiteEditController@getLinkEditModal');

// Leads
Route::get( '/app/leads',                          'Lead\Controller\LeadController@getLeads');
Route::get( '/app/lead',                           'Lead\Controller\LeadController@getLead');
Route::get( '/app/leads/leads-view',               'Lead\Controller\LeadController@getLeadsViewModal');
Route::get( '/app/leads/leads-export',             'Lead\Controller\LeadController@getLeadsExportModal');
Route::post('/app/leads/export',                   'Lead\Controller\LeadController@postLeadsExport');

// Media
Route::get( '/app/media',                          'Media\Controller\MediaController@getBrowser');
Route::get( '/app/browser',                        'Media\Controller\MediaController@elFinder');
Route::get( '/app/editor',                         'Media\Controller\EditorController@getEditor');
Route::get( '/app/editor/templates',               'Media\Controller\EditorController@getTemplates');
Route::get( '/app/editor/template/{tpl}',          'Media\Controller\EditorController@getTemplate');

// Profile, team and subscription
Route::get( '/app/profile',                        'App\Controller\AccountController@getProfile');
Route::post('/app/profile',                        'App\Controller\AccountController@postProfile');
Route::get( '/app/modal/avatar',                   'App\Controller\AccountController@getAvatarModal');
Route::get( '/app/users',                          'App\Controller\AccountController@getUsers');
Route::get( '/app/user',                           'App\Controller\AccountController@getUser');
Route::get( '/app/upgrade',                        'App\Controller\AccountController@getUpgrade');
Route::get( '/app/account',                        'App\Controller\AccountController@getAccount');
Route::get( '/app/order-subscription',             'App\Controller\AccountController@getOrderSubscription');
Route::get( '/app/order-subscription-confirm',     'App\Controller\AccountController@getOrderSubscriptionConfirm');
Route::get( '/app/order-subscription-confirmed',   'App\Controller\AccountController@getOrderSubscriptionConfirmed');
Route::get( '/app/modal/account/invoice',          'App\Controller\AccountController@getInvoiceModal');

// Third-party apps
Route::get( '/app/oauth',                          'App\Controller\oAuthController@getApps');

// Campaigns
Route::get( '/app/campaigns',                      'Campaign\Controller\CampaignController@getCampaigns');
Route::get( '/app/campaign',                       'Campaign\Controller\CampaignController@getCampaign');

// Messages
Route::get( '/app/messages',                       'App\Controller\MessageController@getInbox');
Route::get( '/app/message',                        'App\Controller\MessageController@getMessage');

// Log
Route::get( '/app/log',                            'App\Controller\LogController@getLog');

// Help
Route::get( '/app/help/{item}',                    'App\Core\Help@getHelp');

// Admin
Route::get( '/app/admin/users',                    'App\Controller\AdminController@getUsers');
Route::get( '/app/admin/user',                     'App\Controller\AdminController@getUser');
Route::get( '/app/admin/website',                  'App\Controller\AdminController@getWebsite');
Route::get( '/app/admin/modal/website-settings',   'App\Controller\AdminController@getWebsiteSettingsModal');
Route::get( '/app/admin/cms',                      'App\Controller\AdminController@getCms');

// Demo
// Route::get( '/reset/{key}',                        'App\Controller\InstallationController@reset');

// Only the master reseller can access these routes
if (\Auth::check() && \Auth::user()->reseller_id == 1)
{
  Route::get( '/app/admin/purchases',                'App\Controller\AdminController@getPurchases');
  Route::get( '/app/admin/plans',                    'App\Controller\AdminController@getPlans');
  Route::get( '/app/admin/plan',                     'App\Controller\AdminController@getPlan');
  Route::get( '/app/admin/resellers',                'App\Controller\AdminController@getResellers');
  Route::get( '/app/admin/reseller',                 'App\Controller\AdminController@getReseller');

  // Update
  Route::get( '/update',                             'App\Controller\InstallationController@update');
  Route::get( '/update/now',                         'App\Controller\InstallationController@doUpdate');
}

// Only resellers can access these routes
if (\Auth::check() && \Auth::user()->can('system_management'))
{
  Route::get( '/app/admin/white-label',              'App\Controller\AdminController@getWhiteLabel');
}

/*
 |--------------------------------------------------------------------------
 | Confide routes / authorization
 |--------------------------------------------------------------------------
 */

if (\Config::get('system.allow_registration')) 
{
  Route::get( 'signup',                            'UsersController@create');
  Route::get( 'confirm/{code}',                    'UsersController@confirm');

  Route::group(array('before' => 'csrf'), function()
  {
    Route::post('signup',                          'UsersController@store');
  });
}

Route::get( 'login',                               'UsersController@login');
Route::get( 'forgot_password',                     'UsersController@forgotPassword');
Route::get( 'reset_password/{token}',              'UsersController@resetPassword');
Route::get( 'logout',                              'UsersController@logout');

Route::group(array('before' => 'csrf'), function()
{
  Route::post('login',                             'UsersController@doLogin');
  Route::post('forgot_password',                   'UsersController@doForgotPassword');
  Route::post('reset_password',                    'UsersController@doResetPassword');
});

/*
 |--------------------------------------------------------------------------
 | ElFinder File browser
 |--------------------------------------------------------------------------
 */

if(isset($url_parts['path']) && strpos($url_parts['path'], '/elfinder') !== false)
{
  Route::group(array('before' => 'auth'), function()
  {
    if(Auth::check())
    {
      // Set Root dir + get plan limitation settings
      if(Auth::user()->parent_id == NULL)
      {
        $plan_settings = \Auth::user()->plan->settings;
        $root_dir = \App\Core\Secure::staticHash(Auth::user()->id);
      }
      else
      {
        // Get plan settings from account user
        $parent_user = \User::where('id', '=', \Auth::user()->parent_id)->first();
        $plan_settings = $parent_user->plan->settings;

        // Check if user has admin access to media
        if(\Auth::user()->can('user_management'))
        {
          $root_dir = \App\Core\Secure::staticHash(Auth::user()->parent_id);
        }
        else
        {
          $Punycode = new Punycode();
          $user_dir = $Punycode->encode(Auth::user()->username);
          $root_dir = \App\Core\Secure::staticHash(Auth::user()->parent_id) . '/' . $user_dir;
        }
      }

      $plan_settings = json_decode($plan_settings);
      $disk_space = (isset($plan_settings->disk_space)) ? $plan_settings->disk_space : 1;

      $disk_usage = 0;
  
      if (\Config::get('s3.active', false))
      {
        $client = \Aws\S3\S3Client::factory([
          'key'  => \Config::get('s3.key'),
          'secret' => \Config::get('s3.secret'),
          'region' => \Config::get('s3.region'),
          'version' => 'latest',
          'ACL' => 'public-read',
          'http'  => [
            'verify' => base_path() . '/cacert.pem'
          ]
        ]);
  
        $adapter = new \League\Flysystem\AwsS3v2\AwsS3Adapter($client, \Config::get('s3.media_root_bucket'), null, array('ACL' => 'public-read'));
  
        $filesystem = new \League\Flysystem\Filesystem($adapter);
  
        $user_dir = $filesystem->listContents($root_dir);
  
        foreach ($user_dir as $file)
        {
          $disk_usage += $file['size'];
        }
  
        $disk_usage = round($disk_usage / 1048576, 2);
      }
      else
      {
        $root_dir_full = public_path() . '/uploads/user/' . $root_dir;
        $disk_usage = \Media\Controller\MediaController::GetDirectorySize($root_dir_full); 
        $disk_usage = round($disk_usage / 1048576, 2);
      }
  
      $upload = ($disk_space < $disk_usage) ? false : true;

      $root_dir_full = public_path() . '/uploads/user/' . $root_dir;

      $root = substr(url('/'), strpos(url('/'), \Request::server('HTTP_HOST')));
      $abs_path_prefix = str_replace(\Request::server('HTTP_HOST'), '', $root);

      if(! File::isDirectory($root_dir_full))
      {
        File::makeDirectory($root_dir_full, 0775, true);
      }

      if (\Config::get('s3.active', false))
      {
        $client = Aws\S3\S3Client::factory([
          'key'  => \Config::get('s3.key'),
          'secret' => \Config::get('s3.secret'),
          'region' => \Config::get('s3.region'),
          'version' => 'latest',
          'ACL' => 'public-read',
          'http'  => [
            'verify' => base_path() . '/cacert.pem'
          ]
        ]);

        $adapter = new League\Flysystem\AwsS3v2\AwsS3Adapter($client, \Config::get('s3.media_root_bucket'), null, array('ACL' => 'public-read'));

        // Create root dir if not exists
        $filesystem = new \League\Flysystem\Filesystem($adapter);
        $filesystem->createDir($root_dir);
      }
      elseif (\Config::get('ftp.active', false))
      {
        $adapter = new \League\Flysystem\Adapter\Ftp(
          [
            'host' => \Config::get('ftp.host'),
            'username' => \Config::get('ftp.username'),
            'password' => \Config::get('ftp.password'),
            'root' => \Config::get('ftp.root'),
            'port' => \Config::get('ftp.port'),
            'mode' => \Config::get('ftp.mode')
          ]
        );
      }

      if (\Config::get('s3.active', false))
      {
        $roots = array(
          'public' => array(
            'driver'    => 'Flysystem',
            'path'      => $root_dir,
            'filesystem'  => new \League\Flysystem\Filesystem($adapter),
            'URL'       => \Config::get('s3.url') . '/' . \Config::get('s3.media_root_bucket') . '/' . $root_dir,
            'alias'     => trans('global.my_files'),
            'accessControl' => 'Barryvdh\Elfinder\Elfinder::checkAccess',
            'alias'     => trans('global.my_files'),
            'tmpPath'     => $root_dir_full,
            'tmbPath'     => $root_dir_full . '/.tmb',
            'tmbURL'    => str_replace('http://', '//', url('/uploads/user/' . $root_dir . '/.tmb')),
            'tmbSize'     => '100',
            'tmbCrop'     => false,
            'icon'      => str_replace('http://', '//', url('packages/elfinder/img/volume_icon_local.png'))
          ),
          'local' => array(
            'driver'    => 'LocalFileSystem',
            'path'      => public_path() . '/stock',
            'URL'       => '/stock',
            'defaults'     => array('read' => false, 'write' => false),
            'alias'     => trans('global.stock'),
            'tmbSize'     => '100',
            'tmbCrop'     => false,
            'icon'      => '/packages/elfinder/img/volume_icon_image.png',
            'attributes' => array(
              array(
                'pattern' => '!^.!',
                'hidden'  => false,
                'read'  => true,
                'write'   => false,
                'locked'  => true
              ),
              array(
                'pattern' => '/.tmb/',
                 'read' => false,
                 'write' => false,
                 'hidden' => true,
                 'locked' => false
              ),
              array(
                'pattern' => '/.quarantine/',
                 'read' => false,
                 'write' => false,
                 'hidden' => true,
                 'locked' => false
              )
            )
          )
        );
      }
      elseif (\Config::get('ftp.active', false))
      {
        
      }
      else
      {
        $roots = array(
          array(
            'driver'    => 'LocalFileSystem',
            'path'      => public_path() . '/uploads/user/' . $root_dir,
            'URL'       => $abs_path_prefix . '/uploads/user/' . $root_dir,
            'accessControl' => 'access',
            'tmpPath'     => public_path() . '/uploads/user/' . $root_dir,
               'uploadMaxSize' => '4M',
            'tmbSize'     => '100',
            'tmbCrop'     => false,
            'icon'      => str_replace('http://', '//', url('packages/elfinder/img/volume_icon_local.png')),
            'alias'     => trans('global.my_files'),
            'uploadDeny'  => array('text/x-php'),
            'attributes' => array(
              array(
                'pattern' => '/.tmb/',
                 'read' => false,
                 'write' => false,
                 'hidden' => true,
                 'locked' => false
              ),
              array(
                'pattern' => '/.quarantine/',
                 'read' => false,
                 'write' => false,
                 'hidden' => true,
                 'locked' => false
              ),
              array( // hide readmes
                'pattern' => '/\.(txt|html|php|py|pl|sh|xml)$/i',
                'read'   => false,
                'write'  => false,
                'locked' => true,
                'hidden' => true
              )
            )
          ),
          array(
            'driver'    => 'LocalFileSystem',
            'path'      => public_path() . '/stock',
            'URL'       => '/stock',
            'defaults'     => array('read' => false, 'write' => false),
            'alias'     => trans('global.stock'),
            'tmbSize'     => '100',
            'tmbCrop'     => false,
            'icon'      => '/packages/elfinder/img/volume_icon_image.png',
            'attributes' => array(
              array(
                'pattern' => '!^.!',
                'hidden'  => false,
                'read'  => true,
                'write'   => false,
                'locked'  => true
              ),
              array(
                'pattern' => '/.tmb/',
                 'read' => false,
                 'write' => false,
                 'hidden' => true,
                 'locked' => false
              ),
              array(
                'pattern' => '/.quarantine/',
                 'read' => false,
                 'write' => false,
                 'hidden' => true,
                 'locked' => false
              )
            )
          )
        );
      }

      \Config::set('laravel-elfinder::roots', $roots);

      \Route::get('elfinder/ckeditor4', '\Media\Controller\MediaController@ckEditor');
      \Route::get('elfinder/tinymce', 'Media\Controller\MediaController@showTinyMCE');
      \Route::get('elfinder/standalonepopup/{input_id}/{callback?}', '\Media\Controller\MediaController@popUp');
      \Route::get('elfinder/connector', 'Barryvdh\Elfinder\ElfinderController@showConnector');
      if ($upload) \Route::post('elfinder/connector', 'Barryvdh\Elfinder\ElfinderController@showConnector');
    }
  });
}

/*
 |--------------------------------------------------------------------------
 | 404
 |--------------------------------------------------------------------------
 */

App::missing(function($exception) use($url_parts)
{
  return \Response::view('app.errors.404', [], 404);
});