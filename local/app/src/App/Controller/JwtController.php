<?php
namespace App\Controller;

use Tymon\JWTAuth\Exceptions\JWTException;
/*
|--------------------------------------------------------------------------
| JWT controller
|--------------------------------------------------------------------------
|
| http://developer.avangate.com
|
*/

class JwtController extends \BaseController {

  /**
   * JWT login
   */

  public function postLogin()
  {
    // grab credentials from the request
    $credentials = \Input::only('email', 'password');

    // attempt to verify the credentials
    if (! \Auth::attempt($credentials)) {
      return \Response::json(['error' => 'Email and/or password not recognized.'], 401);
    }

    if (!$user = \Auth::user()) {
      return \Response::json(['error' => 'Email and/or password not recognized.'], 401);
    }

    try {
      $token = \JWTAuth::fromUser($user, ['profile' => ['username' => \Auth::user()->email]]);
    } catch (\JWTException $e) {
      // something went wrong whilst attempting to encode the token
      return \Response::json(['error' => 'could_not_create_token'], 500);
    }

    // all good so return the token
    return \Response::json(compact('token'));
  }

  /**
   * JWT signup
   */

  public function postSignup()
  {
    // grab credentials from the request
    $credentials = \Input::only('email', 'password');

    // attempt to verify the credentials
    if (! \Auth::attempt($credentials)) {
      return \Response::json(['error' => 'invalid_credentials'], 401);
    }

    if (!$user = \Auth::user()) {
      return \Response::json(['error' => 'invalid_credentials'], 401);
    }

    try {
      $token = \JWTAuth::fromUser($user, ['profile' => ['username' => \Auth::user()->email]]);
    } catch (\JWTException $e) {
      // something went wrong whilst attempting to encode the token
      return \Response::json(['error' => 'could_not_create_token'], 500);
    }

    // all good so return the token
    return \Response::json(compact('token'));
  }

  /**
   * Retrieve the Authenticated user from a token
   */

  public function getAuthenticatedUser()
  {
    try {

      if (! $user = \JWTAuth::parseToken()->authenticate()) {
        return \Response::json(['user_not_found'], 404);
      }
    
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
      return \Response::json(['token_expired'], $e->getStatusCode());
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
      return \Response::json(['token_invalid'], $e->getStatusCode());
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
      return \Response::json(['token_absent'], $e->getStatusCode());
    }

    // the token is valid and we have found the user via the sub claim
    return \Response::json(compact('user'));
  }


  /**
   * Get protected route
   */

  public function getProtected()
  {
    $user = \JWTAuth::parseToken()->authenticate();

    return \Response::json(['email' => $user->email]);
  }

  /**
   * Get registered beacons
   */

  public function getBeacons()
  {
    return \Response::json(['beacons' => 1]);
  }


}