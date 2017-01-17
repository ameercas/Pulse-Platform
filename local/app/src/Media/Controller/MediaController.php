<?php
namespace Media\Controller;

/*
|--------------------------------------------------------------------------
| Media controller
|--------------------------------------------------------------------------
|
| Media files related logic
|
*/

class MediaController extends \BaseController {

  /**
   * Construct
   */
  public function __construct()
  {
    if(\Auth::check())
    {
      // Get parent user + get plan limitation settings
      if(\Auth::user()->parent_id == NULL)
      {
        $this->parent_user_id = \Auth::user()->id;
        $plan_settings = \Auth::user()->plan->settings;
        $this->root_dir = \App\Core\Secure::staticHash(\Auth::user()->id);
      }
      else
      {
        $this->parent_user_id = \Auth::user()->parent_id;

        // Get plan settings from account user
        $parent_user = \User::where('id', '=', \Auth::user()->parent_id)->first();
        $plan_settings = $parent_user->plan->settings;

        // Check if user has admin access to media
        if(\Auth::user()->can('user_management'))
        {
          $this->root_dir = \App\Core\Secure::staticHash(Auth::user()->parent_id);
        }
        else
        {
          $Punycode = new Punycode();
          $user_dir = $Punycode->encode(\Auth::user()->username);
          $this->root_dir = \App\Core\Secure::staticHash(\Auth::user()->parent_id) . '/' . $user_dir;
        }
      }
 
      $plan_settings = json_decode($plan_settings);
      $this->disk_space = (isset($plan_settings->disk_space)) ? $plan_settings->disk_space : 1;

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
  
        $user_dir = $filesystem->listContents($this->root_dir);
  
        foreach ($user_dir as $file)
        {
          $disk_usage += $file['size'];
        }
  
        $disk_usage = round($disk_usage / 1048576, 2);
      }
      else
      {
        $root_dir_full = public_path() . '/uploads/user/' . $this->root_dir;
        $disk_usage = $this->GetDirectorySize($root_dir_full); 
        $disk_usage = round($disk_usage / 1048576, 2);
      }
  
      $this->upload = ($this->disk_space < $disk_usage) ? false : true;
    }
    else
    {
      $this->parent_user_id = NULL;
    }
  }

  /**
   * Show media browser
   */
  public function getBrowser()
  {
    $dir = 'packages/elfinder';
    $locale = \Config::get('app.locale');
    $locale = str_replace('pt', 'pt_BR', $locale);

    if (!file_exists(public_path() . "/$dir/js/i18n/elfinder.$locale.js"))
    {
      $locale = false;
    }

    $upload = $this->upload;
/*
    if ($upload)
    {
      return \View::make('app.auth.upgrade');
    }
    else
    {
*/
      return \View::make('app.media.browser', compact('dir', 'locale', 'upload'));
//    }
  }
  /**
   * Get directory size
   */
  public static function GetDirectorySize($path){
    $bytestotal = 0;
    $path = realpath($path);
    if($path!==false){
      foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object){
        $bytestotal += $object->getSize();
      }
    }
    return $bytestotal;
  }

  /**
   * Load elFinder
   */
  public function elFinder()
  {
    // Set Root dir
    if(\Auth::user()->parent_id == NULL)
    {
      $root_dir = \App\Core\Secure::staticHash(\Auth::user()->id);
    }
    else
    {
      $Punycode = new \Punycode();
      $user_dir = $Punycode->encode(\Auth::user()->username);
      $root_dir = \App\Core\Secure::staticHash(\Auth::user()->parent_id) . '/' . $user_dir;
    }

    $root_dir_full = public_path() . '/uploads/user/' . $root_dir;

    if(! \File::isDirectory($root_dir_full))
    {
      \File::makeDirectory($root_dir_full, 0775, true);
    }

    \Config::set('laravel-elfinder::dir', 'uploads/user/' . $root_dir);

    $dir = 'packages/elfinder';
    $locale = \Config::get('app.locale');
    $locale = str_replace('pt', 'pt_BR', $locale);

    if (!file_exists(public_path() . "/$dir/js/i18n/elfinder.$locale.js"))
    {
      $locale = false;
    }

    $upload = $this->upload;

    return \View::make('app.media.elfinder', compact('dir', 'locale', 'upload'));
  }

  /**
   * Load elFinder CKEditor
   */
  public function ckEditor()
  {
    // Set Root dir
    if(\Auth::user()->parent_id == NULL)
    {
      $root_dir = \App\Core\Secure::staticHash(\Auth::user()->id);
    }
    else
    {
      $Punycode = new \Punycode();
      $user_dir = $Punycode->encode(\Auth::user()->username);
      $root_dir = \App\Core\Secure::staticHash(\Auth::user()->parent_id) . '/' . $user_dir;
    }

    $root_dir_full = public_path() . '/uploads/user/' . $root_dir;

    if(! \File::isDirectory($root_dir_full))
    {
      \File::makeDirectory($root_dir_full, 0775, true);
    }

    \Config::set('laravel-elfinder::dir', 'uploads/user/' . $root_dir);

    $dir = 'packages/elfinder';
    $locale = \Config::get('app.locale');
    $locale = str_replace('pt', 'pt_BR', $locale);

    if (!file_exists(public_path() . "/$dir/js/i18n/elfinder.$locale.js"))
    {
      $locale = false;
    }

    $upload = $this->upload;

    return \View::make('app.media.elfinder-ckeditor4', compact('dir', 'locale', 'upload'));
  }

  /**
   * Load elFinder TinyMCE
   */
  public function showTinyMCE()
  {
    // Set Root dir
    if(\Auth::user()->parent_id == NULL)
    {
      $root_dir = \App\Core\Secure::staticHash(\Auth::user()->id);
    }
    else
    {
      $Punycode = new \Punycode();
      $user_dir = $Punycode->encode(\Auth::user()->username);
      $root_dir = \App\Core\Secure::staticHash(\Auth::user()->parent_id) . '/' . $user_dir;
    }

    $root_dir_full = public_path() . '/uploads/user/' . $root_dir;

    if(! \File::isDirectory($root_dir_full))
    {
      \File::makeDirectory($root_dir_full, 0775, true);
    }

    \Config::set('laravel-elfinder::dir', 'uploads/user/' . $root_dir);

    $dir = 'packages/elfinder';
    $locale = \Config::get('app.locale');

    if($locale == 'zh_cn') $locale = 'zh_CN';
    if($locale == 'cn') $locale = 'zh_TW';
    if($locale == 'kr') $locale = 'ko';

    if (!file_exists(public_path() . "/$dir/js/i18n/elfinder.$locale.js"))
    {
      $locale = false;
    }

    $upload = $this->upload;

    return \View::make('app.media.elfinder-tinymce', compact('dir', 'locale', 'upload'));
  }

  /**
   * Load elFinder popup
   */
  public function popUp($input_id, $callback = 'processSelectedFile')
  {
    // Set Root dir
    if(\Auth::user()->parent_id == NULL)
    {
      $root_dir = \App\Core\Secure::staticHash(\Auth::user()->id);
    }
    else
    {
      $Punycode = new \Punycode();
      $user_dir = $Punycode->encode(\Auth::user()->username);
      $root_dir = \App\Core\Secure::staticHash(\Auth::user()->parent_id) . '/' . $user_dir;
    }

    $root_dir_full = public_path() . '/uploads/user/' . $root_dir;

    if(! \File::isDirectory($root_dir_full))
    {
      \File::makeDirectory($root_dir_full, 0775, true);
    }

    \Config::set('laravel-elfinder::dir', 'uploads/user/' . $root_dir);

    $dir = 'packages/elfinder';
    $locale = \Config::get('app.locale');
    $locale = str_replace('pt', 'pt_BR', $locale);

    if (!file_exists(public_path() . "/$dir/js/i18n/elfinder.$locale.js"))
    {
      $locale = false;
    }

    $upload = $this->upload;

    return \View::make('app.media.elfinder-popup', compact('dir', 'locale', 'input_id', 'callback', 'upload'));
  }
}