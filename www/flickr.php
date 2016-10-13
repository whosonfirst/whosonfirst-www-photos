<?php

	include('include/init.php');
	loadlib('wof_utils');
	loadlib('wof_photos');
	loadlib('login');

	login_ensure_loggedin();

	$crumb_venue_fallback = crumb_generate('wof.save');
	$GLOBALS['smarty']->assign("crumb_save_fallback", $crumb_venue_fallback);

	$wof_id = get_int64('id');
	$path = wof_utils_id2relpath($wof_id);
	$url = "https://whosonfirst.mapzen.com/data/$path";
	$rsp = http_get($url);
	if (! $rsp['ok']){
		error_404();
	}

	$geojson = $rsp['body'];
	$feature = json_decode($geojson, 'as hash');
	$props = $feature['properties'];
	$concordances = $props['wof:concordances'];

	if ($GLOBALS['cfg']['user']){
		$crumb_save = crumb_generate('api', 'wof.save');
		$GLOBALS['smarty']->assign('crumb_save', $crumb_save);
	}

	$GLOBALS['smarty']->assign('wof_id', $wof_id);
	$GLOBALS['smarty']->assign('wof_name', $feature['properties']['wof:name']);

	if ($concordances['gp:id']){
		$rsp = wof_photos_flickr_search($concordances['gp:id']);
		$GLOBALS['smarty']->assign('woe_id', $concordances['gp:id']);
		if ($rsp['ok']){
			$GLOBALS['smarty']->assign_by_ref('flickr_photos', $rsp['photos']);
		}
	}

	$rsp = wof_photos_get($wof_id);
	if (! empty($rsp['photos'])){
		$photo = $rsp['photos'][0];
		$GLOBALS['smarty']->assign('primary_photo_id', $photo['info']['id']);
		$GLOBALS['smarty']->assign('primary_photo_src', $photo['src']);
	}

	$GLOBALS['smarty']->display('page_flickr.txt');
	exit();
