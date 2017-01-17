<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
  // http://laravel-tricks.com/tricks/fix-ssl-in-laravel-4-when-server-is-behind-a-load-balancer-or-a-reverse-proxy
  // set the current IP (REMOTE_ADDR) as a trusted proxy
  Request::setTrustedProxies( [ $request->getClientIp() ] );

  // http://stackoverflow.com/questions/19952572/global-variable-in-laravel-4/19953885
  $reseller = \App\Controller\ResellerController::get();
  View::share('reseller', $reseller);

  // Singleton (global) object
  /*
  In any view, use it like

  echo $globals->ssl;

  In any controller, you can use
  
  $globals = App::make('globals');
  echo $globals->ssl;

  */
  App::singleton('globals', function(){

      $ssl = (\Request::server('HTTP_X_FORWARDED_PROTO') != NULL) ? (\Request::server('HTTP_X_FORWARDED_PROTO') == 'https') : \Request::secure();

      $app = new stdClass;
      $app->ssl = $ssl;

      return $app;
  });
  $globals = App::make('globals');
  View::share('globals', $globals);
});

App::after(function($request, $response)
{
	//
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest())
	{
		if (Request::ajax())
		{
			return Response::make('Unauthorized', 401);
		}
		else
		{
      if (! Request::secure() && Request::server('HTTP_X_FORWARDED_PROTO') != NULL)
      {
        return Redirect::guest(secure_url('login'));
      }
      else
      {
        return Redirect::guest('login');
      }
		}
	}
});

/*
|--------------------------------------------------------------------------
| Secure routes
|--------------------------------------------------------------------------
*/

//Route::when('/', 'auth');
Route::when('app*', 'auth');

Entrust::routeNeedsPermission('app/user*',          'user_management');
Entrust::routeNeedsPermission('app/log*',           'user_management');
Entrust::routeNeedsPermission('app/admin/*',        'system_management');
Entrust::routeNeedsPermission('api/v1/admin/*',     'system_management');

Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
    $msg = (isset($_SERVER['HTTP_REFERER'])) ? 'Please click <a href="' . $_SERVER['HTTP_REFERER'] . '">here</a> and try again.' : 'Please go back and refresh the previous page.';
    return View::make('app.errors.general', [
      'subtitle' => 'Session expired',
      'title' => '',
      'msg' => $msg
    ]);
		//throw new Illuminate\Session\TokenMismatchException;
	}
});
