<?php

	loadlib('wof_photos_flickr');
	loadlib('wof_photos_wikipedia');
	loadlib("wof_s3");
	loadlib("slack_bot");
	loadlib("users");

	########################################################################

	function wof_photos_get($wof_id, $type = null){

		$esc_wof_id = intval($wof_id);
		$where_type = '';
		if ($type){
			$esc_type = addslashes($type);
			$where_type = "AND type = '$esc_type'";
		}
		$rsp = db_fetch("
			SELECT *
			FROM photos
			WHERE wof_id = $esc_wof_id
			$where_type
			ORDER BY sort
		");
		if (! $rsp['ok']){
			return $rsp;
		}

		$photos = array();
		foreach ($rsp['rows'] as $photo){
			$photo['info'] = json_decode($photo['info'], 'as hash');
			$photo['src'] = wof_photos_src($photo);
			$photos[] = $photo;
		}

		return array(
			'ok' => 1,
			'photos' => $photos
		);
	}

	########################################################################

	function wof_photos_ext_src($type, $info){
		if ($type == 'flickr'){
			return wof_photos_flickr_src($info);
		} else if ($type == 'wikipedia'){
			return wof_photos_wikipedia_src($info);
		} else {
			return array(
				'ok' => 0,
				'error' => "Cannot get src for unknown photo type $type."
			);
		}
	}

	########################################################################

	function wof_photos_info($type, $ext_id){
		if ($type == 'flickr'){
			return wof_photos_flickr_info($ext_id);
		} else if ($type == 'wikipedia'){
			return wof_photos_wikipedia_info($ext_id);
		} else {
			return array(
				'ok' => 0,
				'error' => "Cannot get info for unknown photo type $type."
			);
		}
	}

	########################################################################

	function wof_photos_save($wof_id, $type, $ext_id){

		$esc_wof_id = intval($wof_id);
		$rsp = db_fetch("
			SELECT *
			FROM photos
			WHERE wof_id = $esc_wof_id
		");
		if (! $rsp['ok']){
			return $rsp;
		}

		foreach ($rsp['rows'] as $photo){
			if ($photo['type'] == $type &&
			    $photo['ext_id'] == $ext_id){
				return array(
					'ok' => 1,
					'already_saved' => 1
				);
			}
		}
		$sort = count($rsp['rows']);

		$rsp = wof_photos_info($type, $ext_id);
		if (! $rsp['ok']){
			return $rsp;
		}
		$info = $rsp['info'];
		$info_json = json_encode($info);

		$relpath = wof_utils_id2relpath($wof_id);
		$reldir = dirname($relpath);
		$dir = "photos/$reldir/$type";
		$ext_filename = $ext_id;
		if ($info['ext_filename']){
			$ext_filename = $info['ext_filename'];
		}
		$basename = "{$wof_id}_{$type}_{$ext_filename}";

		$src_url = wof_photos_ext_src($type, $info);
		$rsp = http_get($src_url);
		if (! $rsp['ok']){
			$rsp['error'] = "Could not load image from source ($type).";
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
		// something something get wof:name
		//$rsp = slack_bot_msg("`$wof_id` $username saved photo for {$props['wof:name']}: $photo_url");

		$esc_user_id = intval($GLOBALS['cfg']['user']['id']);
		$esc_type = addslashes($type);
		$esc_ext_id = addslashes($ext_id);
		$esc_info_json = addslashes($info_json);
		$esc_sort = addslashes($sort);
		$esc_created = addslashes(date('Y-m-d H:i:s'));
		$rsp = db_insert('photos', array(
			'wof_id' =>  $esc_wof_id,
			'user_id' => $esc_user_id,
			'type' =>    $esc_type,
			'ext_id' =>  $esc_ext_id,
			'info' =>    $esc_info_json,
			'sort' =>    $esc_sort,
			'created' => $esc_created
		));
		return $rsp;
	}

	########################################################################

	function wof_photos_src($photo){

		$wof_id = $photo['wof_id'];
		$type = $photo['type'];
		$ext_id = $photo['ext_id'];


		$relpath = wof_utils_id2relpath($wof_id);
		$reldir = dirname($relpath);
		$base_url = "https://whosonfirst.mapzen.com/photos/$reldir";
		$filename = "{$wof_id}_{$type}_{$ext_id}.jpg";

		return "$base_url/{$type}/$filename";
	}

	# the end
