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

	$GLOBALS['smarty']->assign('wof_id', $wof_id);
	$GLOBALS['smarty']->assign('wof_name', $feature['properties']['wof:name']);

	$rsp = wof_photos_get($wof_id);
	if (! empty($rsp['photos'])){
		$photos = $rsp['photos'];
		$GLOBALS['smarty']->assign('primary_photo', array_shift($photos));
		$GLOBALS['smarty']->assign('secondary_photos', $photos);
	}

	$GLOBALS['smarty']->display('page_id.txt');
	exit();
