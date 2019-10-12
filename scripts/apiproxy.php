// Server-client compatible code<?php /*
"use strict";

function getweather(a, b) {
	window.apijson = "";
	window.received = 0;
	window.request = {};
	window.response = "{\"a\":{";
	getregionweather(a, b);
}
function getregionweather(region, next) {
	var target_base = "https://cors-anywhere.herokuapp.com/http://api.openweathermap.org/data/2.5/";
	var target_key = "&units=metric&appid={{OPENWEATHERMAP_APPID}}";
	[ "weather", "forecast" ].forEach(function(type) {
		request[region + type] = new XMLHttpRequest();
		request[region + type].onreadystatechange = function() { processraw(region, type, next); };
		request[region + type].onerror = function() {
			if (apijson == "<offline>") return;
			received = 0;
			window.apijson = "<offline>";
			displayweather();
		};
		request[region + type].open("GET", target_base + type + "?q=" + region + target_key, true);
		request[region + type].send();
	});
}
async function processraw(region, type, next) {
	if (apijson == "<offline>") return;
	if (request[region + type].readyState == 4 && request[region + type].status == 200) {
		response += '"' + type + "\":" + request[region + type].responseText + (++received % 2 == 0 ? '}' : ',');
		if (received == 4) {
			received = 0;
			window.apijson = response + '}';
			displayweather();
		} else if (received == 2) {
			response += ",\"b\":{";
			getregionweather(next);
		}
	}
}/*/
header('Content-Type: application/javascript');
$target_base = "http://api.openweathermap.org/data/2.5/";
$target_key = "&units=metric&appid={{OPENWEATHERMAP_APPID}}";
foreach (array('a', 'b') as $region) {
	$all_req = glob("*.json");
	foreach ($all_req as $cache) {
		if (explode('.', $cache)[0] + 630 < time()) unlink($cache);
	}
	$file_list = glob("*.".$_GET[$region].".json");
	if (count($all_req) >= 299 && count($file_list) == 0) {
		foreach ($all_req as $region_file) {
			$avail_region[] = explode('.', $region_file)[1];
		}
		sort($avail_region);
		echo implode("<br/>", $avail_region);
		return;
	}
	$json_name = $file_list[0];
	if (explode('.', $json_name)[0] + 600 < time() && count($file_list) <= 1) {
		$file_name = time().'.'.$_GET[$region].".json";
		$file_handle = fopen($file_name, "w+");
		foreach (array("weather", "forecast") as $type) {
			$response = file_get_contents($target_base.$type."?q=".$_GET[$region].$target_key);
			if (!$response) {
				fclose($file_handle);
				return;
			}
			$json_data_arr[] = '"'.$type."\":".$response;
		}
		fwrite($file_handle, '{'.implode(',', $json_data_arr).'}');
		fclose($file_handle);
		if (count($file_list) > 0) { unlink($json_name); }
		$json_name = $file_name;
	}
	$region_data = file_get_contents($json_name);
	if ($region_data == "") return;
	$region_data_arr[] = '"'.$region."\":".$region_data;
}
echo '{'.implode(',', $region_data_arr).'}';//*/
