// Server-client compatible code<?php /*
"use strict";

function getweather(a, b) {
	window.hosted = false;
	window.received = 0;
	// array to contain all requests
	window.request = {};
	// prepare to construct JSON
	window.response = "{\"a\":{";
	// test online status
	var online_request = new XMLHttpRequest();
	online_request.timeout = 3000;
	online_request.onreadystatechange = function() {
		if (online_request.readyState == 4 && online_request.status == 200) {
			getregionweather(a, b);
		} else if (online_request.readyState == 4 && online_request.status >= 400) {
			displayweather(true);
		}
	};
	online_request.onerror = online_request.ontimeout = function() {
		displayweather(true);
	}
	online_request.open("HEAD", "https://cors-anywhere.herokuapp.com/https://example.com");
	// header required for CORS-anywhere, but IE does not set it automatically
	online_request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	online_request.send();
}
function getregionweather(region, next) {
	var target_base = "https://cors-anywhere.herokuapp.com/https://api.openweathermap.org/data/2.5/";
	// single quote for setup.sh
	var target_key = '&units=metric&appid={{OPENWEATHERMAP_APPID}}';
	[ "weather", "forecast" ].forEach(function(type) {
		request[region + type] = new XMLHttpRequest();
		request[region + type].onreadystatechange = function() { processraw(region, type, next); };
		request[region + type].onerror = function() {
			// do not call displayweather if it has been called once
			if (apijson == "") return;
			window.apijson = "";
			displayweather(false);
		};
		request[region + type].open("GET", target_base + type + "?q=" + region + target_key, true);
		// header required for CORS-anywhere, but IE does not set it automatically
		request[region + type].setRequestHeader("X-Requested-With", "XMLHttpRequest");
		request[region + type].send();
	});
}
function processraw(region, type, next) {
	// it has errored, do not do anything
	if (apijson == "") return;
	if (request[region + type].readyState == 4 && request[region + type].status == 200) {
		// close each loc section when both weather and forecast is requested
		response += '"' + type + "\":" + request[region + type].responseText + (++received % 2 == 0 ? '}' : ',');
		// all received, close JSON object
		if (received == 4) {
			window.apijson = response + '}';
			displayweather(false);
		} else if (received == 2) {
			// all aloc received, proceed to bloc
			response += ",\"b\":{";
			// call again with bloc data
			getregionweather(next);
		}
	} else if (request[region + type].status >= 400) {
		// if response failed, location does not exist
		window.apijson = "";
		displayweather(false);
	}
}/*/
header('Content-Type: application/javascript');
$target_base = 'https://api.openweathermap.org/data/2.5/';
$target_key = '&units=metric&appid={{OPENWEATHERMAP_APPID}}';
// test online status
ini_set('default_socket_timeout', 2);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://example.com');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($curl);
curl_close($curl);
if (!$response) {
	echo '<server_fault>';
	return;
}
try {
	$db = new PDO('sqlite:apijson.sqlite3');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// remove all JSON older than 10.5 minutes (630s)
	// 30 seconds padding to protect against timer inaccuracy
	$db->prepare('DELETE FROM Response WHERE RequestTime<?;')->execute(array(time() - 630));

	$query = $db->query('SELECT COUNT(*) FROM Response;');
	$row_count = $query->fetch()[0];

	foreach (array('a', 'b') as $region) {
		$clean_name = rawurlencode(mb_strtolower($_GET[$region], 'UTF-8'));
		$clean_name = str_replace('%2C', '%2C%20', $clean_name);
		$clean_name = preg_replace('/(%20)+/', '%20', $clean_name);
		$query = $db->prepare('SELECT * FROM Response WHERE Location=?;');
		$query->execute(array($clean_name));
		$json_list = $query->fetchAll();
		// maximum request reached and no JSON of the region exists, 1 file padding
		if ($row_count >= 299 && count($json_list) === 0) {
			// extract available regions and return the list
			$query = $db->prepare('SELECT Location FROM Response;');
			echo rawurldecode(implode('<br/>', $query->fetchAll()));
			return;
		}
		if (count($json_list) === 0) {
			$query = $db->prepare('INSERT INTO Response'.
				'(Location, RequestTime, Json) VALUES (?, ?, \'\');');
			$query->execute(array($clean_name, time()));
		}
		// for request to happen, it must be older than 10 minutes (600s)
		// and no other requests are happening
		if (count($json_list) === 0 || $json_list[0]['RequestTime'] + 600 < time()) {
			$query = $db->prepare('UPDATE Response SET RequestTime=? WHERE Location=?;');
			$query->execute(array(time(), $clean_name));
			foreach (array('weather', 'forecast') as $type) {
				// get JSON from OpenWeatherMap
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, "$target_base$type?q=$clean_name$target_key");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$response = curl_exec($curl);
				curl_close($curl);
				// if response failed, location does not exist
				if (!$response) { return; }
				// add wrapping around JSON and add to array
				$json_data_arr[] = '"'.$type.'":'.$response;
			}
			$query = $db->prepare('UPDATE Response SET Json=? WHERE Location=?;');
			$query->execute(array('{'.implode(',', $json_data_arr).'}', $clean_name));
		}
		// old JSON will be fetched when the block above is not executed
		$query = $db->prepare('SELECT Json FROM Response WHERE Location=?;');
		$query->execute(array($clean_name));
		$region_data = $query->fetch()[0];
		// region is proven invalid before
		if ($region_data == '') { return; }
		// read and encase in ab wrapper
		$region_data_arr[] = '"'.$region.'":'.$region_data;
	}
	// wrap all around JSON
	echo '{'.implode(',', $region_data_arr).'}';
} catch(PDOException $e) { echo '<server_fault>'; }//*/
