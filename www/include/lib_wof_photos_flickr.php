<?php

	loadlib("flickr_api");

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

		$photos = $rsp['rsp']['photos']['photo'];
		foreach ($photos as $index => $photo){
			$photos[$index]['ext_id'] = $photo['id'];
		}

		return array(
			'ok' => 1,
			'photos' => $photos
		);
	}

	########################################################################

	function wof_photos_flickr_src($info){
		extract($info);
		$size = 'z';
		return "https://farm{$farm}.staticflickr.com/{$server}/{$id}_{$secret}_{$size}.jpg";
	}

	########################################################################

	function wof_photos_flickr_info($ext_id){
		$rsp = flickr_api_call("flickr.photos.getInfo", array(
			'api_key' => $GLOBALS['cfg']['flickr_api_key'],
			'photo_id' => $ext_id
		));
		if (! $rsp['ok']){
			$rsp['error'] = 'Could not getInfo from Flickr.';
			return $rsp;
		}
		$info = $rsp['rsp']['photo'];
		return array(
			'ok' => 1,
			'info' => $info
		);
	}

	# the end
