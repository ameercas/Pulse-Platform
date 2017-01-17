<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>{{ $reseller->app_name }}</title>
	  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <meta http-equiv="cleartype" content="on">
    <link rel="stylesheet" href="{{ url('/assets/css/mobile.css?v=' . Config::get('version.version')) }}" />
    <link rel="stylesheet" href="{{ url('themes/_boilerplate/assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ url('themes/_boilerplate/assets/css/custom.css') }}" />
    <script src="{{ url('/assets/js/mobile.js?v=' . Config::get('version.version')) }}"></script>
  <body animation="slide-left-right-ios7">

    <div class="bar bar-header bar-assertive">
      <h1 class="title">{{ $reseller->app_name }}</h1>
    </div>

    <div style="padding:75px 20px 20px">
      <p class="text-center">{{ trans('app-store.main_text1', ['app_name' => '<strong>' . $reseller->app_name . '</strong>']) }}</p>
      <h3 class="text-center">{{ trans('app-store.main_text2') }}</h3>

      <a href="{{ $download_link }}" class="button button-positive button-block button-large" style="margin-top:20px">
        <i class="icon ion-social-{{ $os }}"></i> &nbsp; {{ trans('app-store.download_button', ['app_name' => '<strong>' . $reseller->app_name . '</strong>']) }}
      </a>
      <a href="{{ url('mobile/' . $local_domain . '?continue=1') }}" class="button button-stable button-block button-small" style="margin-top:30px">
        {{ trans('app-store.continue_button') }}
      </a>
    </div>

  </body>
</html>