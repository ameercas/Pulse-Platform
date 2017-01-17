<?php
namespace Web\Controller;

use Auth, View, Input;

/*
|--------------------------------------------------------------------------
| Export controller
|--------------------------------------------------------------------------
|
| App Export related logic
|
*/

class ExportController extends \BaseController {

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
   * Download package
   */
  public function getDownloadHtml($sl)
  {
    $qs = \App\Core\Secure::string2array($sl);

    $site = \Web\Model\Site::where('id', '=', $qs['site_id'])->where('user_id', '=', $this->parent_user_id)->first();
    $site_dir = storage_path() . '/userdata/exports/site_' . $site->id;

    $slugify = new \Slugify();
    $site_name = $slugify->slugify($site->name);

    $filename = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $site_name);

    if(count($site) > 0)
    {
      // Create directory for export
      $export_dir = storage_path() . '/userdata/exports/site_' . $site->id;
      if(\File::isDirectory($export_dir))
      {
        // Dir exists, empty before exporting again
        \File::cleanDirectory($export_dir);
      }

      \File::makeDirectory($export_dir . '/www/assets/css', 0777, true);
      \File::makeDirectory($export_dir . '/www/assets/fonts', 0777, true);
      \File::makeDirectory($export_dir . '/www/assets/images', 0777, true);
      \File::makeDirectory($export_dir . '/www/assets/js', 0777, true);

      // Copy IE js
      \File::copy(public_path() . '/blocks/assets/js/ie.min.js', $export_dir . '/www/assets/js/ie.min.js');

      // Copy fonts
      \File::copy(public_path() . '/assets/fonts/fontawesome-webfont.eot', $export_dir . '/www/assets/fonts/fontawesome-webfont.eot');
      \File::copy(public_path() . '/assets/fonts/fontawesome-webfont.svg', $export_dir . '/www/assets/fonts/fontawesome-webfont.svg');
      \File::copy(public_path() . '/assets/fonts/fontawesome-webfont.ttf', $export_dir . '/www/assets/fonts/fontawesome-webfont.ttf');
      \File::copy(public_path() . '/assets/fonts/fontawesome-webfont.woff', $export_dir . '/www/assets/fonts/fontawesome-webfont.woff');
      \File::copy(public_path() . '/assets/fonts/fontawesome-webfont.woff2', $export_dir . '/www/assets/fonts/fontawesome-webfont.woff2');
      \File::copy(public_path() . '/assets/fonts/iconsmind-line.eot', $export_dir . '/www/assets/fonts/iconsmind-line.eot');
      \File::copy(public_path() . '/assets/fonts/iconsmind-line.svg', $export_dir . '/www/assets/fonts/iconsmind-line.svg');
      \File::copy(public_path() . '/assets/fonts/iconsmind-line.ttf', $export_dir . '/www/assets/fonts/iconsmind-line.ttf');
      \File::copy(public_path() . '/assets/fonts/iconsmind-line.woff', $export_dir . '/www/assets/fonts/iconsmind-line.woff');

      // Get index html
      $site_root = url('/web/' . $site->local_domain . '?published');

      \Web\Controller\ExportController::parseUrl($site, $site_root, $export_dir);

      // Get real path for our folder
      $rootPath = $export_dir . '/www';
      $zipFile = $export_dir . '/html.zip';
  
      // Initialize archive object
      $zip = new \ZipArchive();
      $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
  
      // Create recursive directory iterator
      /** @var SplFileInfo[] $files */
      $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($rootPath),
        \RecursiveIteratorIterator::LEAVES_ONLY
      );
  
      foreach ($files as $name => $file)
      {
        // Skip directories (they would be added automatically)
        if (! $file->isDir())
        {
          // Get real and relative path for current file
          $filePath = $file->getRealPath();
          $relativePath = substr($filePath, strlen($rootPath) + 1);
      
          // Add current file to archive
          $zip->addFile($filePath, $relativePath);
        }
      }
  
      // Zip archive will be created only after closing object
      $zip->close();
  
      return \Response::download($export_dir . '/html.zip', $filename . '-html.zip', ['Content-Type: application/zip']);

      // Clear generated files after zip is served
      \App::finish(function($request, $response) use ($export_dir, $filename)
      {
        \File::deleteDirectory($export_dir . '/www', false);
      });
    }
  }

  /**
   * Parse url, extract and combine js, css, images
   */
  public static function parseUrl($site, $url, $export_dir)
  {
    $client = new \GuzzleHttp\Client();

    $response = $client->get($url);

    $html = $response->getBody()->getContents();
    $html = preg_replace('#(?<=<!-- Piwik -->)(.*?)(?=<!-- End Piwik Code -->)#ms', '', $html);
    $html = str_replace('<!-- Piwik -->', '', $html);
    $html = str_replace('<!-- End Piwik Code -->', '', $html);
    $html = preg_replace('/(<*[^>]*data-block=)"[^>]+"([^>]*>)/', '', $html);
    $html = str_replace(url('blocks/assets/js/ie.min.js'), 'assets/js/ie.min.js', $html);

    $dom = new \DOMDocument();

    // Prevent errors
    libxml_use_internal_errors(true);

    //avoid the whitespace after removing the node
    $dom->preserveWhiteSpace = false;
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

    $head = $dom->getElementsByTagName('head');
    $html_remove = array();
    $head_append = array();

    // Remove link tags (stylesheet, icons), combine stylesheets
    $links = $dom->getElementsByTagName('link');
    $image = 1;

    foreach ($links as $link)
    {
      $rel = $link->getAttribute('rel');

      if ($rel == 'stylesheet')
      {
        $href = $link->getAttribute('href');

        $response = $client->get($href);
        $css = $response->getBody();

        $url_parsed = parse_url($href);
        $url_path = $url_parsed['path'];

        $script_name = basename($url_path);
        $script_name = str_replace('.css', '', $script_name);

        if (str_contains($href, 'site/global-css')) {
          $script_name = 'global';
        } elseif (str_contains($href, 'custom/site.global.css')) {
          $script_name = 'custom-global';
        }

        // Get images from css, rewrite css paths
        $file_paths = \Web\Controller\ExportController::extract_css_urls($css);

        if (isset($file_paths['property']))
        {
          foreach ($file_paths['property'] as $file_path)
          {
            if (! starts_with($file_path, '../fonts') && ! str_contains($file_path, '#'))
            {
              $copy = true;

              if (starts_with($file_path, 'data:'))
              {
                $copy = false;

                // Create image dir
                if (! \File::isDirectory($export_dir . '/www/assets/images/')) \File::makeDirectory($export_dir . '/www/img/', 0777, true);

                $data = explode(',', $file_path);
                $file_path2 = '' . $image . '.jpg';
                \File::put($export_dir . '/www/assets/images/' . $file_path2, base64_decode($data[1]));
                $image++;
              }

              // Create image dir
              if(! \File::isDirectory($export_dir . '/www/assets/images/')) \File::makeDirectory($export_dir . '/www/img/', 0777, true);

              // Copy image
              $file_path2 = \Web\Controller\ExportController::parsePath($file_path, $url_path);

              if ($copy && \Web\Controller\ExportController::url_validate($file_path2)) 
              {
                \File::copy($file_path2, $export_dir . '/www/assets/images/' . basename($file_path2));
              }

              // Replace in css
              $css = str_replace($file_path, '../images/' . basename($file_path2), $css);
            }
          }
        }

        if ($css != '')
        {
          \File::put($export_dir . '/www/assets/css/' . $script_name . '.css', $css);
          $head_append[] = '<link rel="stylesheet" href="assets/css/' . $script_name . '.css"/>';
        }
        
        $string = $link->ownerDocument->saveHTML($link);
        $html_remove[] = $string;
      }
      else
      {
        $string = $link->ownerDocument->saveHTML($link);
        $html_remove[] = $string;
      }
    }

    // Remove js tags & combine
    $scripts = $dom->getElementsByTagName('script');

    foreach ($scripts as $script)
    {
      $src = $script->getAttribute('src');

      if ($src != '')
      {
        $response = $client->get($src);
        $js = $response->getBody();

        $url_parsed = parse_url($src);
        $url_path = $url_parsed['path'];

        $script_name = basename($url_path);
        $script_name = str_replace('.js', '', $script_name);

        if (str_contains($src, 'site/global-js/')) {
          $script_name = 'global';
        } elseif (str_contains($src, 'custom/site.global.js')) {
          $script_name = 'custom-global';
        }

        if ($js != '')
        {
          \File::put($export_dir . '/www/assets/js/' . $script_name . '.js', $js);
          $head_append[] = '<script src="js/' . $script_name . '.js"></script>';
        }
      }

      if ($src != '')
      {
        $string = $script->ownerDocument->saveHTML($script);
        $html_remove[] = $string;
      }
    }

    // Parse images
    preg_match_all('/<img(?:.*)src=["\'](.*?)["\']/i', $html, $match);
    preg_match_all('/:url\(["\'](.*?)["\']/i', $html, $match2);
    $match[1] = array_merge($match[1], $match2[1]);

    $match3 = \Web\Controller\ExportController::extract_css_urls($html);
    if (isset($match3['property'])) {
      $_match = array_merge($match[1], $match3['property']);
      $match[1] = $_match;
    }

    $image = 1;
    if (isset($match[1]) && ! empty($match[1]))
    {
      foreach($match[1] as $url)
      {
        $src = $url;
        $target_src = $src;

        $url_parsed = parse_url($src);
        $url_path = $url_parsed['path'];
        $copy = true;

        // Copy image
        if (str_contains($src, '?'))
        {
          parse_str($url_parsed['query'], $url_parts);
          unset($url_parts['img']);
          $target_src = implode('-', $url_parts) . '-' . basename($src);
        }
        else
        {
          if (starts_with($url, 'data:'))
          {
            $copy = false;

            $data = explode(',', $url);
            $target_src = $image . '.jpg';
            \File::put($export_dir . '/www/assets/images/' . $target_src, base64_decode($data[1]));
            $image++;
          }
          elseif (! \File::isFile($src)) 
          {
            $src = url($url);
            $target_src = basename($src);
          }
        }

        // Copy image
        $src2 = \Web\Controller\ExportController::parsePath($src, $url_path);

        if ($copy && \Web\Controller\ExportController::url_validate($src2)) 
        {
          \File::copy($src2, $export_dir . '/www/assets/images/' . $target_src);
        }

        // Replace 
        $image_replace[$url] = 'assets/images/' . $target_src;
        //$html = str_replace($url, '../img/' . $target_src, $html);
      }
    }

    // Append head elements
    foreach ($head_append as $append)
    {
      $fragment = $dom->createDocumentFragment();
      $fragment->appendXML($append);

      $head->item(0)->appendChild($fragment);
    }

    $html = $dom->saveHTML();
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    //$html = html_entity_decode($html);

    // Remove elements from html
    foreach ($html_remove as $remove)
    {
      $html = str_replace(trim($remove), '', $html);
    }

    // Replace image text
    if (isset($image_replace))
    {
      foreach ($image_replace as $replace => $replace_with)
      {
        $html = str_replace($replace, $replace_with, $html);
        //$html = str_replace(htmlentities($replace), $replace_with, $html);
      }
    }

    // Format html
    $indenter = new \Gajus\Dindent\Indenter();
    $html = $indenter->indent($html);

    \File::put($export_dir . '/www/index.html', $html);
  }

  /**
   * Parse file path to copyable url
   */
  public static function parsePath($path, $url_path)
  {
    // Check if image is base64 encoded
    if (starts_with($path, 'data:')) return '';

    if (str_contains($path, '../'))
    {
      $dirs = substr_count($path, '../');
      $path = \Web\Controller\ExportController::dirname2($url_path, $dirs + 1) . '/' . str_replace('../', '', $path);
      $path = url($path);
    }
    
    // Make url if path starts with /
    if (starts_with($path, '/')) $path = url($path);

    return $path;
  }

   /*
  * @return boolean
  * @param  string $link
  * @desc   Test url for availability (HTTP-Code: 200)
  */
  public static function url_validate( $link )
  {    
    $url_parts = @parse_url( $link );

    if ( empty( $url_parts["host"] ) ) return( false );

    if ( !empty( $url_parts["path"] ) )
    {
      $documentpath = $url_parts["path"];
    }
    else
    {
      $documentpath = "/";
    }

    if ( !empty( $url_parts["query"] ) )
    {
      $documentpath .= "?" . $url_parts["query"];
    }

    $host = $url_parts["host"];
    $port = isset($url_parts["port"]) ? $url_parts["port"] : 80;
    // Now (HTTP-)GET $documentpath at $host";

    if (empty( $port ) ) $port = "80";
    $socket = @fsockopen( $host, $port, $errno, $errstr, 30 );
    if (!$socket)
    {
      return(false);
    }
    else
    {
      fwrite ($socket, "HEAD ".$documentpath." HTTP/1.0\r\nHost: $host\r\n\r\n");
      $http_response = fgets( $socket, 22 );
      
      if ( preg_match("/200 OK/", $http_response, $regs ) )
      {
        return(true);
        fclose( $socket );
      } else
      {
//        echo "HTTP-Response: $http_response<br>";
        return(false);
      }
    }
  }

  /**
   * Multiple dirs up
   */
  public static function dirname2( $path, $depth = 2 )
  {
    for ($d=1 ; $d <= $depth ; $d++)
      $path = dirname( $path );
    
    return $path;
  }

  /**
   * Extract URLs from CSS text.
   */
  public static function extract_css_urls($text)
  {
    $urls = array( );
   
    $url_pattern   = '(([^\\\\\'", \(\)]*(\\\\.)?)+)';
    $urlfunc_pattern = 'url\(\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
    $pattern     = '/(' .
       '(@import\s*[\'"]' . $url_pattern   . '[\'"])' .
      '|(@import\s*'    . $urlfunc_pattern . ')'    .
      '|('        . $urlfunc_pattern . ')'    .  ')/iu';
    if ( !preg_match_all( $pattern, $text, $matches ) )
      return $urls;
   
    // @import '...'
    // @import "..."
    foreach ( $matches[3] as $match )
      if ( !empty($match) )
        $urls['import'][] = 
          preg_replace( '/\\\\(.)/u', '\\1', $match );
   
    // @import url(...)
    // @import url('...')
    // @import url("...")
    foreach ( $matches[7] as $match )
      if ( !empty($match) )
        $urls['import'][] = 
          preg_replace( '/\\\\(.)/u', '\\1', $match );
   
    // url(...)
    // url('...')
    // url("...")
    foreach ( $matches[11] as $match )
      if ( !empty($match) )
        $urls['property'][] = 
          preg_replace( '/\\\\(.)/u', '\\1', $match );
   
    return $urls;
  }
}