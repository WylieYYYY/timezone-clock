"use strict";
// MIME header<?php header('Content-Type: application/javascript'); ?>

function disableselection(element) {
	if (typeof element.onselectstart != 'undefined') {
		element.onselectstart = function() { return false; };
	} else if (typeof element.style.MozUserSelect != 'undefined') {
		element.style.MozUserSelect = 'none';
	} else {
		element.onmousedown = function() { return false; };
	}
}
function settextbutton() {
	disableselection(document.getElementById("adate"));
	disableselection(document.getElementById("bdate"));
}

function showlocpopup() {
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
function changeweather() {
	window.aloc = document.getElementById("alocinput").value;
	window.bloc = document.getElementById("blocinput").value;
	try {
		localStorage.setItem("aloc", aloc);
		localStorage.setItem("bloc", bloc);
		localStorage.setItem("contrast", document.getElementById("contrast")
				.checked.toString());
	} catch (e) {}
	document.getElementById("s").textContent = "";
	refreshweather();
	hide("locform");
	hide("locformblur");
}

function refreshweather() {
	if (!document.getElementById("contrast").checked) document.body.style.backgroundImage = "";
	else document.body.style.backgroundImage = "url(\"images/prism.png\")";
	window.apijson = "";
	window.fchilow['b'][4] = setTimeout(function() {
		window.apijson = "<offline>";
		displayweather();
	}, 10000);
	// Server-client compatible code<?php /*
	getweather(aloc, bloc);/*/ echo "\n"?>
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if (request.readyState == 4 && request.status == 200) {
			var response = request.responseText.substring(32);
			if (response) window.apijson = response;
			else window.apijson = null;
			displayweather();
		}
	};
	request.onerror = function() {
		window.apijson = "<offline>";
		displayweather();
	};
	request.open("GET", "scripts/apiproxy.php?a=" + aloc + "&b=" + bloc + "&r=" + moment().utc(), true);
	request.send();//*/
}

function displayweather() {
	clearTimeout(fchilow['b'][4]);
	if (apijson && apijson.charAt(0) == '{') {
		window.apijson = JSON.parse(apijson);
		[ 'a', 'b' ].forEach(function(region) {
			var daytime = moment().unix() > apijson[region]["weather"]["sys"]["sunrise"] &&
				moment().unix() < apijson[region]["weather"]["sys"]["sunset"];
			document.getElementById(region + "weather").setAttribute("class", "owf owf-"
				+ apijson[region]["weather"]["weather"][0]["id"] + (daytime ? "-d" : "-n"));
			var tempstr = Math.round(apijson[region]["weather"]["main"]["temp"]).toString();
			document.getElementById(region + "temp").textContent = '\xa0' + (tempstr.length < 3 ? '\xa0' : '') + tempstr
				+ (tempstr.length < 2 ? '\xa0' : '') + "\xb0\x43";
			var templow = Math.round(apijson[region]["weather"]["main"]["temp_min"]).toString();
			var temphi = Math.round(apijson[region]["weather"]["main"]["temp_max"]).toString();
			document.getElementById(region + "hilow").textContent = templow +
				(templow == temphi ? (templow.length < 2 ? '\xa0' : '') :
				(templow.length < 2 ? '\xa0' : '') + '-' + (temphi.length < 2 ? '\xa0' : '') + temphi) + "\xb0\x43";
			var roughtz = Math.round(apijson[region]["weather"]["timezone"] / 10800) * 10800 - 32400;
			for (var firstfc = 0; (apijson[region]["forecast"]["list"][firstfc]["dt"] + roughtz) % 86400 != 0; firstfc++);
			for (var i = 0; firstfc + i * 8 < 40; i++) {
				fc[region][i] = apijson[region]["forecast"]["list"][firstfc + i * 8]["weather"][0]["id"];
				fcdate[region][i] = moment.unix(apijson[region]["forecast"]["list"][firstfc + i * 8]["dt"])
					.add(apijson[region]["weather"]["timezone"], 's').utc().format("dd");
				templow = Math.round(apijson[region]["forecast"]["list"][firstfc + i * 8]["main"]["temp_min"]);
				temphi = Math.round(apijson[region]["forecast"]["list"][firstfc + i * 8]["main"]["temp_max"]);
				fchilow[region][i] = templow + (templow == temphi ? '' : '-' + temphi) + "\xb0";
			}
		});
	} else {
		document.getElementById("locformblur").onclick = function() { changeweather(); };
		document.getElementById("loctitle").textContent = "Try Again";
		if (apijson == "<offline>") {
			document.getElementById("availtitle").innerHTML = "<br/>Offline,<br/>check connection.";
			setTimeout(changeweather, 60000);
		} else if (apijson) {
			document.getElementById("availtitle").innerHTML = "<br/>Server busy,<br/>currently available regions:";
			document.getElementById("availloc").innerHTML = "<br/>" + apijson;
			show("availloc");
		}
		else document.getElementById("availtitle").innerHTML = "<br/>Location unavailable,<br/>please check the city you inputted.";
		show("availtitle");
		show("locform");
		show("locformblur");
	}
}
