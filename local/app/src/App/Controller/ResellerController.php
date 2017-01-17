<?php
namespace App\Controller;

/*
|--------------------------------------------------------------------------
| Reseller controller
|--------------------------------------------------------------------------
|
| Reseller related logic
|
*/

class ResellerController extends \BaseController {

  /**
   * Get reseller
   */
  public static function get()
  {
    $url_parts = parse_url(\URL::current());
    $domain = str_replace('www.', '', $url_parts['host']);

    $reseller = \App\Model\Reseller::where(function ($query) use($domain) {
      $query->where('domain', $domain)
          ->orWhere('domain', 'www.' . $domain)
          ->orWhere('domain', '');
      })->where(function ($query) use($domain) {
        $query->where('active', 1);
      })->first();

    // Check if it's a custom domain
    if ($reseller == NULL)
    {
      $app = \Mobile\Model\App::where('domain', '=', $domain)
          ->orWhere('domain', '=', 'www.' . $domain)
          ->first();

      $site = empty($app) ? \Web\Model\Site::where('domain', '=', $domain)
          ->orWhere('domain', '=', 'www.' . $domain)
          ->first() : [];

      if(! empty($app))
      {
        // Get reseller by user
        $user = \User::find($app->user_id);
        if (! empty($user)) {
          $reseller = \App\Model\Reseller::find($user->reseller_id);
        }
      }

      if(! empty($site))
      {
        // Get reseller by user
        $user = \User::find($site->user_id);
        if (! empty($user)) {
          $reseller = \App\Model\Reseller::find($user->reseller_id);
        }
      }
    }

    if ($reseller == NULL)
    {
      $reseller = \App\Model\Reseller::where(function ($query) use($domain) {
        $query->where('domain', '*');
        })->where(function ($query) use($domain) {
          $query->where('active', 1);
        })->first();
    }

    // Settings
    if ($reseller != NULL)
    {
      if ($reseller->settings != '')
      {
        $reseller->settings = json_decode($reseller->settings);
      }
      else
      {
        $reseller->settings = new \stdClass;
      }

      // Default settings
      if (! isset($reseller->settings->ssl)) $reseller->settings->ssl = false;
      if (! isset($reseller->settings->AVGAFFILIATE)) $reseller->settings->AVGAFFILIATE = '';
    }

    return ($reseller == NULL) ? false : $reseller;
  }
}