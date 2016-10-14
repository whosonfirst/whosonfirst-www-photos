<?php

	loadlib('wof_utils');
	loadlib('wof_photos');
	loadlib('wof_photos_flickr');
	loadlib('wof_photos_wikipedia');

	########################################################################

	function api_wof_photos_get(){

		$wof_id = post_int32('wof_id');
		$type = post_str('type');
		$rsp = wof_photos_get($wof_id, $type);

		api_output_ok($rsp);
	}

	########################################################################

	function api_wof_photos_save(){

		$user = $GLOBALS['cfg']['user'];
		if (! $user) {
			api_output_error(400, 'You must be logged in save a photo.');
		}

		$wof_id = post_int32('wof_id');
		$type = post_str('type');
		$ext_id = post_str('ext_id');
		$rsp = wof_photos_save($wof_id, $type, $ext_id);

		api_output_ok($rsp);
	}

	########################################################################

	# the end
