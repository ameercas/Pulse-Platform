<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Stapler Storage Driver
	|--------------------------------------------------------------------------
	|
	| The default mechanism for handling file storage.  Currently Stapler supports
	| both file system and Amazon S3 as options. (file | s3)
	|
	*/

	'storage' => (\Config::get('s3.active', false)) ? 's3' : 'filesystem',

];