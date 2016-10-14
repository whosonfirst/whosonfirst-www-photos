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

	if ($concordances['wk:page']){
		$rsp = wof_photos_wikipedia_search($concordances['wk:page']);
		$GLOBALS['smarty']->assign('wk_page', $concordances['wk:page']);
		if ($rsp['ok']){
			$GLOBALS['smarty']->assign_by_ref('wikipedia_photos', $rsp['photos']);
		}
	}

	$rsp = wof_photos_get($wof_id, 'wikipedia');
	if (! empty($rsp['photos'])){
		$ext_ids = array();
		foreach ($rsp['photos'] as $photo){
			$ext_ids[] = $photo['ext_id'];
		}
		$GLOBALS['smarty']->assign('photos_saved', $ext_ids);
	}

	$GLOBALS['smarty']->display('page_wikipedia.txt');
	exit();
