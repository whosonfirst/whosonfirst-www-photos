<?php

	loadlib("s3");

	########################################################################

	function wof_s3_put_file($rel, $path, $args=array(), $more=array()) {

		if (! file_exists("{$rel}{$path}")) {
			return array(
				'ok' => 0,
				'error' => "'{$rel}{$path}' not found."
			);
		}

		$data = file_get_contents("{$rel}{$path}");
		$path = "data/$path";

		return wof_s3_put_data($data, $path, $args, $more);
	}

	########################################################################
	
	function wof_s3_put_data($data, $path, $args=array(), $more=array()) {

		$bucket = array(
			'id' => $GLOBALS['cfg']['aws']['s3_bucket'],
			'key' => $GLOBALS['cfg']['aws']['access_key'],
			'secret' => $GLOBALS['cfg']['aws']['access_secret'],
		);

		$args = array_merge(array(
			'id' => $path,
			'data' => $data
		), $args);

		$rsp = s3_put($bucket, $args, $more);
		return $rsp;
	}

	# the end
