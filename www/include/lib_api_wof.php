<?php

	loadlib('wof_utils');
	loadlib('wof_photos');

	########################################################################

	function api_wof_get_photos(){

		api_utils_features_ensure_enabled(array('photos'));

		$wof_id = post_int32('wof_id');
		$rsp = wof_photos_get($wof_id);

		api_output_ok($rsp);
	}

	########################################################################

	function api_wof_assign_flickr_photo(){

		$user = $GLOBALS['cfg']['user'];
		if (! $user) {
			api_output_error(400, 'You must be logged in assign a Flickr photo.');
		}

		$wof_id = post_int32('wof_id');
		$flickr_id = post_int32('flickr_id');
		$rsp = wof_photos_assign_flickr_photo($wof_id, $flickr_id);

		api_output_ok($rsp);
	}

	########################################################################

	# the end
