// Server-client compatible code<?php /*
"use strict";

function getweather(a, b) {
	// non-empty string to avoid location not found trigger
	window.apijson = "init";
	window.received = 0;
	// array to contain all requests
	window.request = {};
	// prepare to construct JSON
	window.response = "{\"a\":{";
	// test online status
	var online_request = new XMLHttpRequest();
	online_request.onreadystatechange = function() {
		if (online_request.readyState == 4 && online_request.status == 200) {
			getregionweather(a, b);
		} else if (online_request.status >= 400) {
			window.apijson = "<offline>";
			displayweather(false);
		}
	};
	online_request.onerror = function() {
		window.apijson = "<offline>";
		displayweather(false);
	}
	online_request.open("HEAD", "https://cors-anywhere.herokuapp.com/https://example.com");
	// header required for CORS-anywhere, but IE does not set it automatically
	online_request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	online_request.send();
}
function getregionweather(region, next) {
	var target_base = "https://cors-anywhere.herokuapp.com/https://api.openweathermap.org/data/2.5/";
	var target_key = "&units=metric&appid={{OPENWEATHERMAP_APPID}}";
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
$target_base = "https://api.openweathermap.org/data/2.5/";
$target_key = "&units=metric&appid={{OPENWEATHERMAP_APPID}}";
// test online status
ini_set('default_socket_timeout', 2);
$response = file_get_contents("https://example.com");
if (!$response) {
	echo "<server_fault>";
	return;
}
foreach (array('a', 'b') as $region) {
	$all_req = glob("*.json");
	// remove all JSON older than 10.5 minutes (630s)
	// 30 seconds padding to protect against timer inaccuracy
	foreach ($all_req as $cache) {
		if (explode('.', $cache)[0] + 630 < time()) unlink($cache);
	}
	$file_list = glob("*.".$_GET[$region].".json");
	// maximum request reached and no JSON of the region exists, 1 file padding
	if (count($all_req) >= 299 && count($file_list) == 0) {
		// extract available regions and return the list
		foreach ($all_req as $region_file) {
			$avail_region[] = explode('.', $region_file)[1];
		}
		sort($avail_region);
		echo implode("<br/>", $avail_region);
		return;
	}
	$json_name = $file_list[0];
	// for request to happen, it must be older than 10 minutes (600s)
	// and no other requests are happening
	if (explode('.', $json_name)[0] + 600 < time() && count($file_list) <= 1) {
		$file_name = time().'.'.$_GET[$region].".json";
		$file_handle = fopen($file_name, "w+");
		foreach (array("weather", "forecast") as $type) {
			// get JSON from OpenWeatherMap
			$response = file_get_contents($target_base.$type."?q=".$_GET[$region].$target_key);
			// if response failed, location does not exist
			if (!$response) {
				fclose($file_handle);
				return;
			}
			// add wrapping around JSON and add to array
			$json_data_arr[] = '"'.$type."\":".$response;
		}
		fwrite($file_handle, '{'.implode(',', $json_data_arr).'}');
		fclose($file_handle);
		// if there are old JSON, remove them (between 10 minutes and 10.5 minutes)
		if (count($file_list) > 0) { unlink($json_name); }
		$json_name = $file_name;
	}
	// json_name will be the old JSON when the block above is not executed
	$region_data = file_get_contents($json_name);
	// region is proven invalid before
	if ($region_data == "") return;
	// read and encase in ab wrapper
	$region_data_arr[] = '"'.$region."\":".$region_data;
}
// wrap all around JSON
echo '{'.implode(',', $region_data_arr).'}';//*/
