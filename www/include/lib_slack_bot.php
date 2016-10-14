<?php

	loadlib('http');

	########################################################################

	function slack_bot_msg($msg){
		if (! $GLOBALS['cfg']['enable_feature_slack_bot']){
			return;
		}

		$url = $GLOBALS['cfg']['slack_bot_webhook_url'];
		$postfields = json_encode(array(
			'text' => $msg
		));
		$headers = array(
			'Content-Type: application/json'
		);
		$rsp = http_post($url, $postfields, $headers);
		return $rsp;
	}

	# the end
