"use strict";
// MIME header<?php header('Content-Type: application/javascript'); ?>

// https://stackoverflow.com/a/2310809
function disableselection(element) {
	if (typeof element.onselectstart != 'undefined') {
		element.onselectstart = function() { return false; };
	} else if (typeof element.style.MozUserSelect != 'undefined') {
		element.style.MozUserSelect = 'none';
	} else {
		element.onmousedown = function() { return false; };
	}
}
// called from index.html
function settextbutton() {
	disableselection(document.getElementById("adate"));
	disableselection(document.getElementById("bdate"));
}

function showlocpopup() {
	// actively opened popup, click off is assumed to be cancel
	document.getElementById("locformblur").onclick = function() {
		hide('locform');
		hide('locformblur');
	};
	document.getElementById("loctitle").textContent = "Change Location";
	document.getElementById("availtitle").innerHTML = "";
	document.getElementById("availloc").innerHTML = "";
	hide("availtitle");
	hide("availloc");
	show("locform");
	show("locformblur");
}

function restorejson() {
	try {
		var savedjson = sessionStorage.getItem("apijson");
		if (savedjson) {
			// non-empty invisible control character that is higher than 59
			document.getElementById("s").textContent = '\x7f';
			window.apijson = savedjson;
			// trigger display of old information, will be override by body onload
			displayweather(false);
			sessionStorage.removeItem("apijson");
		}
	} catch (e) {}
}

function applycontrast() {
	// default to CSS value when it is an empty string, empty URL for no background image
	if (!document.getElementById("contrast").checked) document.body.style.backgroundImage = "";
	else document.body.style.backgroundImage = "url()";
}

function changesetting() {
	applycontrast();
	try {
		localStorage.setItem("contrast", document.getElementById("contrast")
			.checked.toString());
	} catch (e) {}
	// contrast change does not require request and does not need timeout
	if (aloc == document.getElementById("alocinput").value &&
		bloc == document.getElementById("blocinput").value) {
		hide("locform");
		hide("locformblur");
		return;
	}
	// if previous request is not finished, it will be a timeout PID (positive integer)
	if (timeoutpid != 0) {
		document.getElementById("availtitle").innerHTML = "<br/>Please wait<br/>until last request<br/>is finished.";
		show("availtitle");
		return;
	}
	// update values and save to local storage if hosted
	window.aloc = document.getElementById("alocinput").value;
	window.bloc = document.getElementById("blocinput").value;
	try {
		localStorage.setItem("aloc", aloc);
		localStorage.setItem("bloc", bloc);
	} catch (e) {}
	// set second to nothing, triggers main function to sync time with apijson
	document.getElementById("s").textContent = "";
	refreshweather(true);
	hide("locform");
	hide("locformblur");
}

function refreshweather(loc_changed) {
	window.timeoutpid = setTimeout(function() {
		// server's fault if we know that it is displaying
		if (document.getElementById("s").textContent == "") {
			window.apijson = "<offline>";
			displayweather(false);
		} else displayweather(true);
	}, 15000);
	// Server-client compatible code<?php /*
	getweather(aloc, bloc);/*/ echo "\n"?>
	// if location changed, we must notify the user, exclude auto-refresh
	if (loc_changed && document.getElementById("s").textContent != '\x7f') window.apijson = "";
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if (request.readyState == 4 && request.status == 200) {
			// skip "// Server-client compatible code" indicator
			var response = request.responseText.substring(32);
			if (response == "<server_fault>" ||
				document.getElementById("s").textContent != "" &&
				response == "") {
				// host or weather server offline
				displayweather(true);
			}
			// else it may be empty or available locations
			else {
				window.apijson = response;
				displayweather(false);
			}
		} else if (request.readyState == 4 && request.status >= 400) {
			if (document.getElementById("s").textContent == "") {
				window.apijson = "<offline>";
				displayweather(false);
			} else displayweather(true);
		}
	};
	request.onerror = function() {
		if (document.getElementById("s").textContent == "") {
				window.apijson = "<offline>";
				displayweather(false);
		} else displayweather(true);
	};
	// parameter r to force request instead of using cached file
	request.open("GET", "scripts/apiproxy.php?a=" + aloc + "&b=" + bloc + "&r=" + moment().utc(), true);
	request.send();//*/
}

function displayweather(server_fault) {
	// remove offline message timeout, responses start with curly bracket is valid JSON
	clearTimeout(timeoutpid);
	if (typeof apijson == "string" && apijson.charAt(0) == '{') {
		document.getElementById("fctime").style.backgroundColor = "lawngreen";
		window.apijson = JSON.parse(apijson);
		[ 'a', 'b' ].forEach(function(region) {
			// calculate daytime to change icon style
			var daytime = moment().unix() > apijson[region]["weather"]["sys"]["sunrise"] &&
				moment().unix() < apijson[region]["weather"]["sys"]["sunset"];
			// current weather
			document.getElementById(region + "weather").setAttribute("class", "owf owf-"
				+ apijson[region]["weather"]["weather"][0]["id"] + (daytime ? "-d" : "-n"));
			var tempstr = Math.round(apijson[region]["weather"]["main"]["temp"]).toString();
			// pad temperature string but keep it centered
			document.getElementById(region + "temp").textContent = tempstr + (tempstr.length < 2 ? '\xa0' : '') + "\xb0\x43";
			var templow = Math.round(apijson[region]["weather"]["main"]["temp_min"]).toString();
			var temphi = Math.round(apijson[region]["weather"]["main"]["temp_max"]).toString();
			// pad each side, remove one side if equal
			window.suppinfo[region][0] = templow + (templow == temphi ? (templow.length < 2 ? '\xa0' : '') :
				(templow.length < 2 ? '\xa0' : '') + '/' + (temphi.length < 2 ? '\xa0' : '') + temphi) + "\xb0\x43";
			window.suppinfo[region][1] = apijson[region]["weather"]["main"]["humidity"] + "\xa0%";
			// round timezone by 3 hour interval (10800s), offset to 9AM (32400s)
			var roughtz = Math.round(apijson[region]["weather"]["timezone"] / 10800) * 10800 - 32400;
			// skip entries until next epoch day (86400s) in the entry thus next 9AM in the timezone
			for (var firstfc = 0; (apijson[region]["forecast"]["list"][firstfc]["dt"] + roughtz) % 86400 != 0; firstfc++);
			// assuming next 9AM is always less than 24hrs away, this will always iterate 5 times
			for (var i = 0; firstfc + i * 8 < 40; i++) {
				fc[region][i] = apijson[region]["forecast"]["list"][firstfc + i * 8]["weather"][0]["id"];
				fcdate[region][i] = moment.unix(apijson[region]["forecast"]["list"][firstfc + i * 8]["dt"])
					.add(apijson[region]["weather"]["timezone"], 's').utc().format("dd");
				templow = Math.round(apijson[region]["forecast"]["list"][firstfc + i * 8]["main"]["temp_min"]);
				temphi = Math.round(apijson[region]["forecast"]["list"][firstfc + i * 8]["main"]["temp_max"]);
				fchilow[region][i] = templow + (templow == temphi ? '' : '/' + temphi) + "\xb0";
			}
		});
	} else if (server_fault && typeof apijson == "object") {
		// is JSON object, server/client has no connection to Internet and not first time
		document.getElementById("fctime").style.backgroundColor = "yellow";
		[ 'a', 'b' ].forEach(function(region) {
			document.getElementById(region + "temp").textContent = "--\xb0\x43";
		});
	} else {
		// in a failed state, click off is assumed to be confirm
		document.getElementById("locformblur").onclick = function() { changesetting(); };
		document.getElementById("loctitle").textContent = "Try Again";
		// offline message timeout reached, retry every minute
		if (apijson == "<offline>" || (server_fault && !hosted)) {
			// can also be client offline on the first time
			document.getElementById("availtitle").innerHTML = "<br/>Offline,<br/>check connection.";
			setTimeout(changesetting, 60000);
		} else if (apijson) {
			// response is not JSON or <offline>, must be a list of available regions
			document.getElementById("availtitle").innerHTML = "<br/>Server busy,<br/>currently available regions:";
			document.getElementById("availloc").innerHTML = "<br/>" + apijson;
			show("availloc");
		} else if (server_fault) {
			// first time enter or loc changed but server faulted
			document.getElementById("availtitle").innerHTML = "<br/>Server fault,<br/>contact administrator.";
		}
		// empty response, request to OpenWeatherMap failed, incorrect parameters
		else document.getElementById("availtitle").innerHTML = "<br/>Location unavailable,<br/>please check the city you inputted.";
		show("availtitle");
		show("locform");
		show("locformblur");
	}
	// request is allowed again
	window.timeoutpid = 0;
}
