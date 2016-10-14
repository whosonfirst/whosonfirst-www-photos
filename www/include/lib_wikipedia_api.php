<?php

	loadlib("http");

	$GLOBALS['cfg']['wikipedia_api_endpoint'] = 'https://en.wikipedia.org/w/api.php';

	########################################################################

	function wikipedia_api_query($args) {
		$args['action'] = 'query';
		if (! $args['format']) {
			$args['format'] = 'json';
		}
		$query = http_build_query($args);
		$url = $GLOBALS['cfg']['wikipedia_api_endpoint'] . "?$query";
		$rsp = http_get($url);
		if (! $rsp['ok']) {
			return $rsp;
		}

		$rsp['rsp'] = json_decode($rsp['body'], 'as hash');
		return $rsp;
	}

	# the end
