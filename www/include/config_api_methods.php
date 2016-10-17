<?php

	########################################################################

	$GLOBALS['cfg']['api']['methods'] = array_merge(array(

		"wof.photos_get" => array (
			"description" => "Finds photos for a WOF record.",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_wof",
			"requires_crumb" => 0,
			"request_method" => "POST",
			"parameters" => array(
				array("name" => "wof_id", "description" => "The WOF ID.", "documented" => 1, "required" => 1),
				array("name" => "type", "description" => "Optional source filter ('flickr', 'wikipedia', etc.)", "documented" => 1, "required" => 0)
			)
		),

		"wof.photos_save" => array (
			"description" => "Assigns a photo to a WOF record.",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_wof",
			"requires_crumb" => 0,
			"request_method" => "POST",
			"parameters" => array(
				array("name" => "wof_id", "description" => "The WOF ID.", "documented" => 1, "required" => 1),
				array("name" => "type", "description" => "The source type ('flickr', 'wikipedia', etc.)", "documented" => 1, "required" => 1),
				array("name" => "ext_id", "description" => "The external source ID.", "documented" => 1, "required" => 1)
			)
		),

		"wof.photos_set_primary" => array (
			"description" => "Sets the primary photo for a WOF record.",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_wof",
			"requires_crumb" => 0,
			"request_method" => "POST",
			"parameters" => array(
				array("name" => "wof_id", "description" => "The WOF ID.", "documented" => 1, "required" => 1),
				array("name" => "photo_id", "description" => "The photo ID.", "documented" => 1, "required" => 1)
			)
		),

		"api.spec.methods" => array (
			"description" => "Return the list of available API response methods.",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_spec"
		),

		"api.spec.formats" => array(
			"description" => "Return the list of valid API response formats, including the default format",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_spec"
		),

		"test.echo" => array(
			"description" => "A testing method which echo's all parameters back in the response.",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_test"
		),

		"test.error" => array(
			"description" => "Return a test error from the API",
			"documented" => 1,
			"enabled" => 1,
			"library" => "api_test"
		),

	), $GLOBALS['cfg']['api']['methods']);

	########################################################################

	# the end
