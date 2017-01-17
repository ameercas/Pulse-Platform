<?php
namespace App\Controller;

/*
|--------------------------------------------------------------------------
| Avangate controller
|--------------------------------------------------------------------------
|
| http://developer.avangate.com
|
*/

class AvangateController extends \BaseController {

  /**
   * LCN (License Change Notification) Get
   * https://example.com/api/v1/avangate/lcn
   */

  public function getLcn()
  {
    // Array with product code (MWP001, etc.)
    $IPN_PCODE = \Request::get('IPN_PCODE', []);

    $request = \Request::all();
    $html = '';

    foreach ($request as $key => $val)
    {
      $html .= $key . ': ' . $val . '<br>';
    }

    \Mail::send('emails.web.lead', ['body' => $html], function($message)
    {
      $message->from(\Config::get('mail.from.address'), 'Avangate Debug');
      $message->to([\Config::get('mail.from.address')])->subject('[GET] Avangate LCN GET log');
    });
  }

  /**
   * LCN (License Change Notification) Posts
   * https://example.com/api/v1/avangate/lcn
   */

  public function postLcn()
  {
    // Avangate hash validation, get all POST parameters except HASH
    $request = \Request::except('HASH');
    $remote_hash = \Request::get('HASH', '');

    $date = date('YmdGis');
    $secret_key = \Config::get('avangate.secret_key');

    $hmac_string = $this->array_to_string($request);
    $hash = $this->hmac($secret_key, $hmac_string);

    if ($remote_hash == $hash)
    {
      // It's a valid request
      $action = '';

      $EXPIRATION_DATE = \Request::get('EXPIRATION_DATE', '');
      $DISABLED = \Request::get('DISABLED', '');
      $EXPIRED = \Request::get('EXPIRED', '');
      $EMAIL = \Request::get('EMAIL', '');
      $LICENSE_PRODUCT = \Request::get('LICENSE_PRODUCT', '');
      $LICENSE_CODE = \Request::get('LICENSE_CODE', '');
      $COUNTRY = \Request::get('COUNTRY', '');
      $CITY = \Request::get('CITY', '');

      $user_id = \Request::get('EXTERNAL_CUSTOMER_REFERENCE', '');

      if ($user_id != '')
      {
        $remote_id = \Request::get('AVANGATE_CUSTOMER_REFERENCE', '');
  
        $user = \User::where('id', $user_id)->first();
        
        if (! empty($user))
        {
          $action = 'Plan id was ' . $user->plan_id;

          $user->remote_id = $remote_id;
  
          if ($EXPIRATION_DATE != '') $user->expires = $EXPIRATION_DATE;
  
          $plans = \App\Model\Plan::orderBy('sort', 'asc')->get();
  
          if ($DISABLED == 0 && $EXPIRED == 0)
          {
            // Switch plan
            $plan_id = 0;

            foreach ($plans as $plan)
            {
              $settings = json_decode($plan->settings);
              $product_id = (isset($settings->product_id)) ? $settings->product_id : '';
              if ($product_id == $LICENSE_PRODUCT)
              {
                $action .= ' but  ' . $plan->id . ' is found in the plans loop, ';
                $plan_id = $plan->id;
                break;
              }
            }

            if ($plan_id > 0)
            {
              $action .= ' is set to ' . $plan_id;

              $user->plan_id = $plan_id;
            }
            else
            {
              // Switch to free account
              $action .= ' is set to free {} ' . $plans{0}->id;

              $user->plan_id = $plans{0}->id;
              //$user->expires = NULL;
            }
          }
          else
          {
            // Switch to free account
            if ($DISABLED != 0)
            {
              $action .= ' is set to free ' . $plans{0}->id . ' because DISABLED was ' . $DISABLED;
            }
            else
            {
              $action .= ' is set to free ' . $plans{0}->id . ' because EXPIRED was ' . $EXPIRED;
            }

            $user->plan_id = $plans{0}->id;
            //$user->expires = NULL;
          }

          $user->settings = \App\Core\Settings::json(array(
            'EMAIL' => $EMAIL,
            'COUNTRY' => $COUNTRY,
            'CITY' => $CITY,
            'LICENSE_CODE' => $LICENSE_CODE,
            'LICENSE_PRODUCT' => $LICENSE_PRODUCT
          ), $user->settings);
  
          $user->save();
        }
      }

      $your_signature = $this->hmac($secret_key, $this->array_to_string([$LICENSE_CODE, $EXPIRATION_DATE, $date]));

      echo '<EPAYMENT>' . $date . '|' . $your_signature . '</EPAYMENT>';

      /**
       * Debug mail
       */

      if(\Config::get('avangate.debug_mail', false)) { 
        $html = '';
    
        $html .= 'action: ' . $action . '<br>';
        $html .= 'hash: ' . $hash . '<br>';
        $html .= 'your_signature: ' . $your_signature . '<br>';
    
        foreach ($request as $key => $val)
        {
          $html .= $key . ': ' . $val . '<br>';
        }
    
        \Mail::send('emails.web.lead', ['body' => $html], function($message)
        {
          $message->from(\Config::get('mail.from.address'), 'Avangate Debug');
          $message->to([\Config::get('mail.from.address')])->subject('[POST] Valid Avangate LCN log');
        });
      }
    }
  }

  /**
   * IPN Get
   * https://example.com/api/v1/avangate/ipn
   */

  public function getIpn()
  {
    $request = \Request::all();
    $html = '';

    foreach ($request as $key => $val)
    {
      $html .= $key . ': ' . $val . '<br>';
    }

    /**
     * Debug mail
     */

    if(\Config::get('avangate.debug_mail', false)) { 
      \Mail::send('emails.web.lead', ['body' => $html], function($message)
      {
        $message->from(\Config::get('mail.from.address'), 'Avangate Debug');
        $message->to([\Config::get('mail.from.address')])->subject('[GET] Avangate IPN log');
      });
    }
  }

  /**
   * IPN Posts
   * https://example.com/api/v1/avangate/ipn
   */

  public function postIpn()
  {
    $request = \Request::all();
    $html = '';

    foreach ($request as $key => $val)
    {
      $html .= $key . ': ' . $val . '<br>';
    }

    /**
     * Debug mail
     */

    if(\Config::get('avangate.debug_mail', false)) { 
      \Mail::send('emails.web.lead', ['body' => $html], function($message)
      {
        $message->from(\Config::get('mail.from.address'), 'Avangate Debug');
        $message->to([\Config::get('mail.from.address')])->subject('[POST] Avangate IPN log');
      });
    }
  }

  public static function hmac($key, $data){
    $b = 64; // byte length for md5
    if (strlen($key) > $b) {
     $key = pack('H*',md5($key));
    }
    $key  = str_pad($key, $b, chr(0x00));
    $ipad = str_pad('', $b, chr(0x36));
    $opad = str_pad('', $b, chr(0x5c));
    $k_ipad = $key ^ $ipad ;
    $k_opad = $key ^ $opad;
    return md5($k_opad  . pack('H*',md5($k_ipad . $data)));
  }

  public static function array_to_string($data){
    $return = '';
    
    if(!is_array($data)){
      $return    .= strlen($data).$data;
    }
    else{
      foreach($data as $val){
        $return    .= strlen($val).$val;
      }        
    }
    return $return;
  }
}