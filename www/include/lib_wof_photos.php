<?php

	loadlib("wof_s3");
	loadlib("flickr_api");
	loadlib("slack_bot");
	loadlib("users");

	########################################################################

	function wof_photos_flickr_search($woe_id){
		$rsp = flickr_api_call('flickr.photos.search', array(
			'api_key' => $GLOBALS['cfg']['flickr_api_key'],
			'woe_id' => $woe_id,
			'safe_search' => 2,
			'license' => '4,5,6,7,8'
		));
		if (! $rsp['ok']){
			return $rsp;
		}

		return array(
			'ok' => 1,
			'photos' => $rsp['rsp']['photos']['photo']
		);
	}

	########################################################################

	function wof_photos_flickr_src($photo, $size = 'z'){
		extract($photo);
		return "https://farm{$farm}.staticflickr.com/{$server}/{$id}_{$secret}_{$size}.jpg";
	}

	########################################################################

	function wof_photos_assign_flickr_photo($wof_id, $flickr_id){

		if (! $GLOBALS['cfg']['user']['id'] ||
		    ! $wof_id ||
		    ! $flickr_id){
			return array(
				'ok' => 0,
				'error' => "You must be logged in, and you must provide a wof_id and flickr_id."
			);
		}

		$rsp = flickr_api_call("flickr.photos.getInfo", array(
			'api_key' => $GLOBALS['cfg']['flickr_api_key'],
			'photo_id' => $flickr_id
		));
		if (! $rsp['ok']){
			return array(
				'ok' => 0,
				'error' => "Could not load getInfo from Flickr."
			);
		}

		$esc_wof_id = intval($wof_id);
		$esc_user_id = intval($GLOBALS['cfg']['user']['id']);
		$type = 'flickr'; // For now we only know about Flickr
		$info = $rsp['rsp']['photo'];

		$info_json = $rsp['raw'];
		$esc_info_json = addslashes($info_json);

		// For now we only do one primary photo
		$rsp = db_write("
			DELETE FROM photos
			WHERE wof_id = $esc_wof_id
		");
		if (! $rsp['ok']){
			return $rsp;
		}

		$rsp = wof_photos_save($wof_id, $type, $info_json, $esc_user_id);
		if (! $rsp['ok']){
			return $rsp;
		}

		$rsp = db_insert('photos', array(
			'wof_id' => $esc_wof_id,
			'user_id' => $esc_user_id,
			'type' => $type,
			'info' => $esc_info_json,
			'sort' => 0,
			'created' => date('Y-m-d H:i:s')
		));
		return $rsp;
	}

	########################################################################

	function wof_photos_get($wof_id){
		$esc_wof_id = intval($wof_id);
		$rsp = db_fetch("
			SELECT *
			FROM photos
			WHERE wof_id = $esc_wof_id
			ORDER BY sort
		");
		if (! $rsp['ok']){
			return $rsp;
		}

		$photos = array();
		foreach ($rsp['rows'] as $photo){
			$photo['info'] = json_decode($photo['info'], 'as hash');
			if ($photo['type'] == 'flickr'){
				$photo['src'] = wof_photos_src($photo);
			}
			$photos[] = $photo;
		}

		return array(
			'ok' => 1,
			'photos' => $photos
		);
	}

	########################################################################

	function wof_photos_save($wof_id, $type, $info_json, $user_id){

		$relpath = wof_utils_id2relpath($wof_id);
		$reldir = dirname($relpath);
		$dir = "photos/$reldir";
		$info = json_decode($info_json, 'as hash');

		if ($type == 'flickr'){
			$dir .= '/flickr';
			$basename = "{$wof_id}_flickr_{$info['photo']['id']}";
			$src_url = wof_photos_flickr_src($info['photo']);
		}

		$rsp = http_get($src_url);
		if (! $rsp['ok']){
			return $rsp;
		}

		$photo_data = $rsp['body'];

		$rsp = wof_s3_put_data($info_json, "$dir/$basename.json");
		if (! $rsp['ok']){
			return $rsp;
		}

		$rsp = wof_s3_put_data($photo_data, "$dir/$basename.jpg");
		if (! $rsp['ok']){
			return $rsp;
		}

		$photo_url = $rsp['url'];
		$photo_name = basename($photo_url);
		$user = users_get_by_id($user_id);
		$username = $user['username'];

		//$rsp = slack_bot_msg("`$wof_id` $username saved photo for {$props['wof:name']}: $photo_url");
		return $rsp;
	}

	########################################################################

	function wof_photos_src($photo){

		$wof_id = $photo['wof_id'];
		$type = $photo['type'];
		$info = $photo['info'];

		$relpath = wof_utils_id2relpath($wof_id);
		$reldir = dirname($relpath);
		$base_url = "https://whosonfirst.mapzen.com/photos/$reldir";

		if ($type == 'flickr'){
			$filename = "{$wof_id}_flickr_{$info['photo']['id']}.jpg";
		}

		return "$base_url/{$type}/$filename";
	}

	# the end
