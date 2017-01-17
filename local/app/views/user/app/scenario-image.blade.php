<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta http-equiv="cleartype" content="on">

	<link rel="stylesheet" href="{{ url('/assets/css/mobile.css?v=' . Config::get('version.version')) }}" />
	<link rel="stylesheet" href="{{ url('assets/css/tinymce-ionic.css') }}" />
	<script src="{{ url('/assets/js/mobile.js?v=' . Config::get('version.version')) }}"></script>

	<style type="text/css">
	html, body {
		height:100%;
	}
    body {
        background-image:url("{{ $image }}");
        background-repeat:no-repeat;
        background-position:center center;
        background-attachment:fixed;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;

        /* Small Devices, Tablets */
        @media only screen and (max-width : 1280px) {
            background-attachment:scroll;
        }
    }
    </style>

	<body>

	</body>
</html>