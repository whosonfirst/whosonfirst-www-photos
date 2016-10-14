<?php

	loadlib("wikipedia_api");

	########################################################################

	function wof_photos_wikipedia_search($page){

		$page = str_replace(' ', '_', $page);
		$rsp = wikipedia_api_query(array(
			'titles' => $page,
			'prop' => 'images',
			'imlimit' => 50
		));
		if (! $rsp['ok']){
			return $rsp;
		}

		$photos = array();
		$titles = array();
		foreach ($rsp['rsp']['query']['pages'] as $id => $page){
			foreach ($page['images'] as $image){
				if (preg_match('/\.jpe?g$/i', $image['title'])){
					$title = $image['title'];
					$photos[] = array(
						'ext_id' => $title,
						'type' => 'wikipedia'
					);
					$titles[$title] = count($photos) - 1;
				}
			}
		}

		$rsp = wikipedia_api_query(array(
			'titles' => implode('|', array_keys($titles)),
			'prop' => 'imageinfo',
			'iiprop' => 'url',
			'iiurlwidth' => 640
		));
		if (! $rsp['ok']){
			return $rsp;
		}

		foreach ($rsp['rsp']['query']['pages'] as $id => $page){
			$title = $page['title'];
			$index = $titles[$title];
			foreach ($page['imageinfo'] as $info){
				if (preg_match('/([^\/]+)\.\w+$/', basename($info['url']), $matches)){
					$info['ext_filename'] = $matches[1];
				}
				$photos[$index]['info'] = $info;
				break;
			}
		}

		return array(
			'ok' => 1,
			'photos' => $photos
		);
	}

	########################################################################

	function wof_photos_wikipedia_src($info){
		return $info['thumburl'];
	}

	########################################################################

	function wof_photos_wikipedia_info($ext_id){

		$rsp = wikipedia_api_query(array(
			'titles' => $ext_id,
			'prop' => 'imageinfo',
			'iiprop' => 'url|timestamp|user|userid|comment|canonicaltitle|size|mime',
			'iiurlwidth' => 640
		));
		if (! $rsp['ok']){
			$rsp['error'] = "Could not load info from Wikipedia.";
			return $rsp;
		}

		foreach ($rsp['rsp']['query']['pages'] as $id => $page){
			foreach ($page['imageinfo'] as $info){
				if (preg_match('/([^\/]+)\.\w+$/', basename($info['url']), $matches)){
					$info['ext_filename'] = $matches[1];
				}
				return array(
					'ok' => 1,
					'info' => $info
				);
			}
		}

		return array(
			'ok' => 0,
			'error' => "Could not find info for Wikipedia image $ext_id."
		);
	}
